<?php namespace Look\Conversation\Classes;

use Twig;
use BackendAuth;
use Carbon\Carbon;
use System\Models\File as FileModel;
use October\Rain\Support\Collection;
use October\Rain\Database\Models\DeferredBinding as DeferredBindingModel;

use Backend\Models\User as StaffMemberModel;

use Look\Conversation\Models\Settings;
use Look\Conversation\Models\Status as StatusModel;
use Look\Conversation\Models\Message as MessageModel;
use Look\Conversation\Models\Participant as ParticipantModel;

/**
 * Class MessageService
 */
class MessageService
{
	use \Look\Conversation\Traits\UserHelper;

	/**
	 * Filters the message owner out of a collection of message participants
	 *
	 * @param MessageModel $message
	 * @param Collection $participants
	 * @return Collection
	 */
	public static function removeOwnerFromParticipants($message, $participants)
	{
		return $participants->filter(function ($participant) use ($message) {
			return !static::compareUsers($participant->user, $message->user);
        });
	}

	/**
	 * Returns the correctly formatted display name for the user
	 *
	 * @param UserModel $user
	 * @param bool $proper Return proper name (true) or personalize (You) name (false)
	 * @return string
	 */
	public static function getUserDisplayName($user, $proper = false, $tags = true)
	{
		if (!$proper && static::compareUsers($user, static::getCurrentUser())) {
			return 'You';
		}

		$name = '';
		if ($tags) {
			$tag = '';

			if (Settings::instance()->system_messages_from === $user->id) {
				$tag = '(System)';
			} elseif (static::isClient($user)) {
				$tag = '(Client)';
			} elseif (static::isStaff($user)) {
				$tag = '(Staff)';
			}

			$name = $tag . ' ' . $user->fullName;
		} else {
			$name = $user->fullName;
		}

		return $name;
	}

	/**
	 * Returns a comma-separated list of participant display names from a collection of participants
	 *
	 * @param Collection $participants
	 * @return string
	 */
	public static function getParticipantsNames($participants)
	{
		$result = '';
		$i = 0;
		$numOfRecipients = count($participants);
		foreach ($participants as $participant) {
			$result .= static::getUserDisplayName($participant->user);

			if (++$i !== $numOfRecipients) {
				$result .= ', ';
			}
		}

		return $result;
	}

	/**
	 * Retrieve messages for a provided user
	 *
	 * @param mixed $user
	 * @param array $scopes The scopes to apply
	 * @return Collection
	 */
	public static function getMessages($user = null, $scopes = [])
	{
		$messageModel = new MessageModel();
		$query = $messageModel->query();

		if (!empty($scopes)) {
			foreach ($scopes as $scope) {
				if ($messageModel->methodExists('scope' . studly_case($scope))) {
					$query->$scope($user);
				}
			}
		} else {
			$query->received();
		}

		return $query->withUserStatus($user)->get();
	}

	/**
	 * Gets a MessageModel by id for the provided user - defaults to current
	 *
	 * @param integer $id
	 * @param mixed $user optional
	 * @return MessageModel|null
	 */
	public static function getMessageById($id, $user = null)
	{
		return MessageModel::participantsInclude($user)->find((int) $id);
	}

	/**
	 * Checks to see if the provided user (defaults to current) has access to the provided message
	 *
	 * @param MessageModel $message
	 * @param mixed $user optional
	 * @return bool
	 */
	public static function userCanAccess($message, $user = null)
	{
		$user = static::defaultToCurrentUser($user);
		if ($message->userIsParticipant($user)) {
			return true;
		}
	}


	/**
	 * Check if the provided user is the owner (original sender) of the provided message
	 *
	 * @param MessageModel $message
	 * @param UserModel $user optional
	 * @return bool
	 */
	public static function checkMessageOwner($message, $user = null)
	{
		$user = static::defaultToCurrentUser($user);
		return static::compareUsers($user, $message->user);
	}



	/**
	 * Prepare a message to be a reply to another message
	 *
	 * @param MessageModel $reply
	 * @param MessageModel $parentMessage
	 * @param string $sessionKey Session key to defer binding of participants with
	 * @param UserModel $user optional The user to mark the reply as from
	 * @param bool $skipParticipants Skip adding participants
	 * @return MessageModel
	 */
	public static function prepareReply($reply, $parentMessage, $sessionKey, $user = null, $skipParticipants = false)
	{
		$user = static::defaultToCurrentUser($user);

		// Assign the parent's thread to the reply
		$reply->thread = $parentMessage->thread;

		if (!$skipParticipants) {
			$participants = static::getReplyParticipants($reply, $parentMessage, $sessionKey, $user);
			foreach ($participants as $participant) {
				// NOTE: Model::insert() doesn't return the inserted records, so we can't use it for deferred bindings
				$participant->save();
			}

			$reply->participants()->addMany($participants, $sessionKey);
		}

		return $reply;
	}

	/**
	 * Returns collection of a reply's participants
	 *
	 * @param MessageModel $reply
	 * @param MessageModel $parentMessage
	 * @param string $sessionKey Session key to get already deferred participants
	 * @param UserModel $user optional The user this reply will be from
	 * return Collection $participants
	 */
	public static function getReplyParticipants($reply, $parentMessage, $sessionKey, $user = null)
	{
		$user = static::defaultToCurrentUser($user);
		$reply->user = $user;

		// Get the list of the previous message's participants, remove the current reply's creator, and add the previous message's owner
		$parentRecipients = static::removeOwnerFromParticipants($reply, $parentMessage->participants);

		// Get the list of participants that already exist for the reply
		$recipients = $reply->participants()->withDeferred($sessionKey)->get();

		$participants = new Collection();
		foreach ($parentRecipients as $recipient) {
			$participants->push(new ParticipantModel([
				'user_id'   => $recipient->user_id,
				'user_type' => $recipient->user_type,
			]));
		}
		$participants->push(new ParticipantModel([
			'user_id'   => $parentMessage->user_id,
			'user_type' => $parentMessage->user_type,
		]));

		$mergedRecipients = $participants->merge($recipients);
		$participants = $mergedRecipients->unique(function ($participant) {
			return $participant->user_type.$participant->user_id;
		});

		$participants = static::removeOwnerFromParticipants($reply, $participants);

		return $participants;
	}

	/**
	 * Get the authorized recipient for a client
	 *
	 * @param UserModel $user optional default to current
	 * @return UserModel
	 */
	public static function getClientRecipient($client = null)
	{
		$client = static::defaultToCurrentUser($client);

		if (static::isClient($client)) {
		    return BackendAuth::getUser();
		} else {
			throw new \ApplicationException("The provided user is not a client.");
		}
	}

	/**
	 * Sends a message from the system
	 * NOTE: Seems horrendously ineffecient, why can't we abuse system notifications for this functionality instead?
	 * NOTE: No way to unsend all messages simulatneously, would have to login as system user and unsend each one individually
	 *
	 * @param string $subject Message subject
	 * @param string $body Message body
	 * @param Collection $recipients Collection of User models - Message recipients, only allowed to be clients
	 * @return Collection $messages All messages that were sent
	 */
	public static function sendSystemMessage(string $subject, string $body, Collection $recipients)
	{
		try {
			$systemUserId = Settings::instance()->system_messages_from;
			$systemUser = StaffMemberModel::findOrFail($systemUserId);
		} catch (\Exception $e) {
			throw new \ApplicationException("The assigned system user with an ID of {$systemUserId} could not be found. Please choose another.");
		}

		// Loop through the message recipients and create a new message for each and then send it
		$messages = new Collection();
		foreach ($recipients as $recipient) {
			if (static::isClient($recipient)) {
				// Create the message
				$message = MessageModel::create([
					'subject'   => $subject,
					'body'      => $body,
					'user_id'   => $systemUser->id,
					'user_type' => get_class($systemUser),
				]);

				// Attach the current recipient to the message
				$participants = new Collection();
				$participants->push(PariticipantModel::firstOrCreate([
					'user_id'    => $recipient->id,
					'user_type'  => get_class($recipient),
					'message_id' => $message->id,
				]));
				$message->participants = $participants;

				// Send the message
				$messages->push(static::sendMessage($message));
			}
		}

		return $messages;
	}

	/**
	 * Send a message to the provided recipients
	 *
	 * @param MessageModel $message
	 * @param Collection $recipients
	 * @return MessageModel
	 */
	public static function sendMessage($message)
	{
		// All messages must always be saved before being sent. This will properly setup the list of participants
		if (!$message->exists) {
			throw new \ApplicationException("Messages must be saved before they can be sent.");
		}

		// All messages must have participants (recipients) that aren't just the owner of the message
		$recipients = static::removeOwnerFromParticipants($message, $message->participants()->get());

		// Validate recipients of a client sent message
		if (static::isClient($message->user)) {
		    // Get the allowed recipient of the message
		    $recipient = static::getClientRecipient($message->user);

		    // Find any participants that are not the one allowed
		    $unauthorizedRecipients = $recipients->filter(function ($item) use ($recipient) {
			    return !static::compareUsers($recipient, $item->user);
		    });

		    // Remove all unauthorized recipients
		    if (!$unauthorizedRecipients->isEmpty()) {
			    // Remove from DB
			    ParticipantModel::destroy($unauthorizedRecipients->pluck('id'));
		    }

		    // Allowed recipient isn't in the collection
		    $allowedRecipients = $recipients->where('id', $recipient->id);
		    if ($allowedRecipients->isEmpty()) {
			    $participant = ParticipantModel::firstOrCreate([
			    	'user_id' => $recipient->id,
			    	'user_type' => get_class($recipient),
			    	'message_id' => $message->id,
			    ]);
			    $allowedRecipients->push($participant);
		    }

		    $recipients = $allowedRecipients;
		}

		if ($recipients->isEmpty()) {
			throw new \ApplicationException("Messages must have recipients specified before they can be sent.");
		}

		// Prevent messages from being forwarded to clients
		if (!empty($message->thread->forwarded_from) && $message->client_recipient) {
			throw new \ApplicationException("Messages are not permitted to be forwarded to clients.");
		}

		/*
			We don't want the owner in the participants unless status gets tied to participants
			// Add the owner to the participants if it's not already there.
			ParticipantModel::firstOrNew([
				'message_id' => $message->id,
				'user_id'    => $message->user_id,
				'user_type'  => $message->user_type,
			]);
		*/

		// Extensability
		$message->fireCombinedEvent('beforeSend', [$recipients]);

		// Set the sent datetime value
		$message->sent_at = Carbon::now()->toDateTimeString();
		$message->save();

		// Setup the statuses that make this message sent
		static::bulkSetStatuses($message, $recipients, StatusModel::UNREAD);
		static::markRead($message, $message->user);

		// Extensability
		$message->fireCombinedEvent('send', [$recipients]);

		return $message;
	}

	/**
	 * Unsend a message if it hasn't been marked as read by it's participants yet
	 * NOTE: If any participant has read the message already it cannot be retracted.
	 *
	 * @param $message
	 * @return $message
	 */
	public static function unsendMessage($message)
	{
		$participants = static::removeOwnerFromParticipants($message, $message->participants()->get());

		$blockingParticipants = new Collection();
		$participantsToDelete = new Collection();
		foreach ($participants as $participant) {
			if (!$message->isUnread($participant->user)) {
				$blockingParticipants->push($participant);
			} else {
				$participantsToDelete->push($participant);
			}
		}

		// Extensability
		$message->fireCombinedEvent('beforeUnsend', [$blockingParticipants, $participantsToDelete]);

		if (count($blockingParticipants)) {
			throw new \ApplicationException(sprintf(
				"You can't retract this message, %s %s already read it",
				static::getParticipantsNames($blockingParticipants),
				count($blockingParticipants) === 1 ? 'has' : 'have'
			));
		} else {
			// Delete the existing participants' status records
			foreach ($participantsToDelete as $participant) {
				$status = StatusModel::participant($participant);
				$status->delete();
			}

			static::markDraft($message, true);

			// Extensability
			$message->fireCombinedEvent('unsend');
		}

		return $message;
	}

	/**
	 * Get the status record for the provided message and user
	 *
	 * @param MessageModel $message
	 * @param UserModel $user
	 * @return StatusRecord
	 */
	public static function getStatusRecord($message, $user)
	{
		return $message->statuses()->forUser($user)->first();
	}

	/**
	 * Check that the user's status for the message is the provided status type
	 *
	 * @param MessageModel $message
	 * @param UserModel $user
	 * @param string $status
	 * @return bool
	 */
	public static function checkStatus($message, $user, $status)
	{
		$statusRecord = static::getStatusRecord($message, static::defaultToCurrentUser($user));
		return ($statusRecord && $statusRecord->status === $status);
	}

	/**
	 * Set the status of a message
	 *
	 * @param MessageModel $message
	 * @param string $status
	 * @param UserModel $user
	 * @return StatusModel $statusRecord
	 */
	public static function setStatus($message, $status, $user = null)
	{
		$user = static::defaultToCurrentUser($user);
		if (!static::userCanAccess($message, $user)) {
			throw new \ApplicationException(sprintf(
				"You don't have the permission to set the status of Message #%d to %s for %s",
				$message->id,
				$status,
				static::getUserDisplayName($user, true)
			));
		}

		$statusRecord = $message->status()->forUser($user, true)->first();

		if (!$statusRecord) {
			$statusRecord = new StatusModel();
			$statusRecord->message = $message;
			$statusRecord->user = $user;
		}

		$statusRecord->status = $status;
		$statusRecord->save();

		return $statusRecord;
	}

	/**
	 * Set the status of a message for all provided participants
	 *
	 * @param MessageModel $message
	 * @param Collection $participants
	 * @param string $status
	 * @return Collection
	 */
	public static function bulkSetStatuses($message, $participants, $status) {
		$statuses = new Collection();

		if (!$participants->isEmpty()) {
			foreach ($participants as $participant) {
				$statuses->push(StatusModel::updateOrCreate([
					'message_id' => $message->id,
					'user_id'    => $participant->user_id,
					'user_type'  => $participant->user_type,
				], ['status' => $status]));
			}
		}

		return $statuses;
	}

	/**
	 * Mark a message as a draft
	 * NOTE: Only able to be done to messages that do not already have a status (unless $force is specified), and only applies the status to the owner's status record
	 *
	 * @param MessageModel $message
	 * @param bool $force
	 * @return StatusModel|null
	 */
	public static function markDraft($message, $force = false)
	{
		$status = null;
		$user = $message->user;
		if (!static::getStatusRecord($message, $user) || static::checkStatus($message, $user, StatusModel::DRAFT) || $force) {
			$status = static::setStatus($message, StatusModel::DRAFT, $user);
		}
		return $status;
	}

	/**
	 * Mark a message as unread
	 *
	 * @param MessageModel $message
	 * @param UserModel $user optional
	 * @return StatusModel
	 */
	public static function markUnread($message, $user = null)
	{
		$status = null;
		if (!static::getStatusRecord($message, $user) || static::checkStatus($message, $user, StatusModel::READ)) {
			$status = static::setStatus($message, StatusModel::UNREAD, $user);
		}
		return $status;
	}

	/**
	 * Mark a message as read
	 *
	 * @param MessageModel $message
	 * @param UserModel $user optional
	 * @return StatusModel|null
	 */
	public static function markRead($message, $user = null)
	{
		return static::setStatus($message, StatusModel::READ, $user);
	}

	/**
	 * Mark a message as archived - Soft delete delete drafts when archiving them
	 *
	 * @param MessageModel $message
	 * @param UserModel $user optional
	 * @return StatusModel
	 */
	public static function markArchived($message, $user = null)
	{
		if ($message->isDraft()) {
			$message->delete();
		} else {
			return static::setStatus($message, StatusModel::ARCHIVED, $user);
		}
	}


	/**
	 * Get the amount of messages for a given user & scope (defaults to the logged in user and unread)
	 *
	 * @param UserModel $user optional
	 * @param string $scope optional
	 * @return int $count
	 */
	public static function getScopeCount($user = null, $scope = null)
	{
		$user = static::defaultToCurrentUser($user);
		$scope = $scope ?: 'unread';

		$count = 0;
		$messageModel = new MessageModel();
		if ($messageModel->methodExists('scope' . studly_case($scope))) {
			$count = MessageModel::$scope($user)->count();
		}
		unset($messageModel);
		return $count;
	}

	/**
	 * Validate a message
	 *
	 * @param mixed $message
	 * @return boolean
	 */
	public static function isValidMessage($message)
	{
		return ($message instanceof MessageModel);
	}


	/**
	 * Prepare a message to be forwarded
	 *
	 * @param MessageModel $message The message to be forwarded
	 * @param MessageModel optional $forwardMessage The message object to operate preperations on
	 * @param string $sessionKey Session key for deferred binding
	 * @return MessageModel
	 */
	public static function prepareForwardedMessage($message, $forwardMessage = null, $sessionKey = null)
	{
		$forwardMessage = $forwardMessage ?: new MessageModel();
		$forwardMessage->attributes['subject'] = static::generateForwardSubject($message);
		$forwardMessage->body = static::generateForwardBody($message);

		// Get and depulicate attachments including deferred attachments
		$attachments = $message->attachments()->withDeferred($sessionKey)->get();
		$deferredAttachmentIds = DeferredBindingModel::where('session_key', $sessionKey)
									->where('master_type', 'Look\Conversation\Models\Message')
									->where('master_field', 'attachments')
									->where('slave_type', 'System\Models\File')
									->get()
									->pluck('slave_id');
		$deferredAttachmentDiskNames = FileModel::whereIn('id', $deferredAttachmentIds)->get()->pluck('disk_name');
		$attachments = $attachments->unique('disk_name');


		$preparedAttachments = new Collection();
		foreach ($attachments as $attachment) {
			// Prevent deferring an attachment if it's already been deferred
			if (in_array($attachment->disk_name, $deferredAttachmentDiskNames->all())) {
				continue;
			}

			$file = new FileModel([
				'file_name' => $attachment->file_name,
				'file_size' => $attachment->file_size,
				'content_type' => $attachment->content_type,
				'title' => $attachment->title,
				'description' => $attachment->description,
			]);
			$file->disk_name = $attachment->disk_name;

			$preparedAttachments->push($file);
			$file->save();

			$forwardMessage->attachments()->add($file, $sessionKey);
		}

		return $forwardMessage;
	}



	/** --------------------
	 * Templating functions
	 -------------------- */

	/**
     * Path to templates
     */
    const TEMPLATE_PATH = 'look/conversation/views/templates/';

	/**
	 * Get a specified message template
	 */
	protected static function messageTemplate($template)
	{
		return file_get_contents(plugins_path(static::TEMPLATE_PATH . $template . '.htm'));
	}

	public static function generateForwardSubject($parentMessage)
	{
		return 'Fwd: ' . $parentMessage->subject;
	}

	public static function generateForwardBody($parentMessage)
	{
		/*
			TODO:
				- Add support for attaching most recent file attachments to new message and displaying in form
				- Provide older messages in thread to template to render in recursive blockquotes
		*/

		$messages = new Collection();
		$messages->push($parentMessage);
		$olderSiblings = $parentMessage->olderSiblings()->get();
		if (count($olderSiblings)) {
			foreach ($olderSiblings as $sibling) {
				$messages->push($sibling);
			}
		}

		return Twig::parse(static::messageTemplate('forward'), [
			'messages' => $messages,
		]);
	}
}