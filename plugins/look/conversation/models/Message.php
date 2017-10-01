<?php namespace Look\Conversation\Models;

use Model;
use Purifier; // Look.Essentials

use Backend\Models\User as StaffModel;;

use Look\Conversation\Classes\MessageService;
use Look\Conversation\Models\Thread as ThreadModel;
use Look\Conversation\Models\Template as TemplateModel;
use Look\Conversation\Models\Participant as ParticipantModel;

/**
 * Class Message
 */
class Message extends Model
{
	use \Look\Conversation\Traits\EventHelper;
    use \October\Rain\Database\Traits\SoftDelete;
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\Encryptable;
    use \Look\Conversation\Traits\PolymorphicUserScopes;

    /**
	 * Prefix to remove for local events
	 * If empty will remove first section of event key: 'component.action' global would equal 'action' local
	 * @var string
	 */
	const EVENT_PREFIX = 'look.conversation.message';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'look_conversation_messages';

    /**
     * @var array The attributes that are mass assignable
     */
    protected $fillable = ['subject', 'body', 'user_id', 'user_type'];

    /**
	 * @var array The attributes that are to be mutated to dates
	 */
    protected $dates = ['created_at', 'updated_at', 'sent_at', 'deleted_at'];

    /**
     * @var array The attributes that are to be encrypted
     */
    protected $encryptable = ['body'];

    /*
     * Validation
     */
    public $rules = [
        'body' => 'required',
    ];

    /**
     * @var array Custom attribute names for the validation trait
     */
    public $attributeNames = [
        'body' => 'look.conversation::lang.models.message.body',
    ];


    /*
     * Relations
     */

	public $belongsTo = [
		'thread' => [
			'Look\Conversation\Models\Thread',
			'key' => 'thread_id',
		],
	];

    public $hasOne = [
        'status' => [
            'Look\Conversation\Models\Status',
        ],
        'client_recipient' => [
	        'Look\Conversation\Models\Participant',
	        'key' => 'message_id',
	        'scope' => 'userIsClient',
        ],
    ];

    public $hasMany = [
	    'participants' => [
		    'Look\Conversation\Models\Participant',
		    'key' => 'message_id',
	    ],
	    'statuses' => [
		    'Look\Conversation\Models\Status',
		    'key' => 'message_id',
	    ],
    ];

    public $morphTo = [
	    'user' => [],
    ];

    public $morphedByMany = [
        'staff_recipients' => [
            'Backend\Models\User',
            'name'     => 'user',
            'key'      => 'message_id',
            'otherKey' => 'user_id',
            'table'    => 'look_conversation_participants',
        ],
    ];

    public $attachMany = [
        'attachments' => 'System\Models\File',
    ];


	/*
	 * Model manipulation events
	 */

	public function beforeSave()
	{
		// Attach the message owner
		if (!$this->user) {
			$user = $this->getCurrentUser();
			if ($user) {
				$this->user = $user;
			} else {
				throw new \ApplicationException("You must be logged in to create a message.");
			}
		}

		// Prevent previously sent messages from being modified.
		if ($this->exists && !$this->isDraft()) {
			throw new \ApplicationException("Messages can only be modified while they're drafts.");
		}

		// Initialize thread if not exists already,
		if (!$this->thread) {
			$this->thread = new ThreadModel();
			$this->thread->subject = $this->attributes['subject'];
			$this->thread->save();
		} elseif (count($this->thread->messages) === 1 && $this->exists && !empty($this->attributes['subject'])) {
			// Update the subject on the thread
			$this->thread->subject = $this->attributes['subject'];
			$this->thread->save();
		}

		if (isset($this->attributes['subject'])) {
			// Remove the subject attribute from the message model
			unset($this->attributes['subject']);
		}

		// Clean the message HTML before saving it
		$this->body = Purifier::clean($this->body);
	}

	public function afterSave()
	{
		$attachments = $this->attachments;
		$ids = [];
		$attachments->each(function ($item) use ($ids) {
			if (in_array($item->disk_name, $ids)) {
				$item->delete();
				unset($item);
			} else {
				$ids[] = $item->disk_name;
			}
		});
	}

	public function afterCreate()
	{
		MessageService::markDraft($this);
	}

    // Prevent actually deleting messages
    public function beforeDelete()
    {
	    if (!$this->isDraft()) {
		    $this->archive();
		    return false;
	    }
    }


    /*
	 * Accessors
	 */

	public function getThreadCountAttribute()
	{
		$threadCount = 0;
		if ($this->thread) {
			$threadCount = $this->siblings()->count();
			// Only display count badge for messages with more than just themselves in their thread
			if ($threadCount >= 1) {
				if ($this->isSent()) {
					$threadCount++; // Increment to include this message
				}
			}
		}
		return $threadCount;
	}

	public function getSubjectAttribute()
	{
		if ($this->thread) {
			return $this->thread->subject;
		}
	}

	public function setSubjectAttribute($value)
	{
		$this->attributes['subject'] = $value;
	}

	public function getSubjectWithThreadCountAttribute()
	{
		return $this->thread_count ? "{$this->subject} ({$this->thread_count})" : $this->subject;
	}

	public function getRecipientNamesAttribute()
	{
		return MessageService::getParticipantsNames($this->participants()->get());
	}

	public function authorName(array $options = [])
	{
		$options['proper']      = isset($options['proper']) ? $options['proper'] : false;
		$options['threadCount'] = isset($options['threadCount']) ? $options['threadCount'] : false;
		$options['tags']        = isset($options['tags']) ? $options['tags'] : true;

		$name = '';
		if ($this->user) {
			$name = MessageService::getUserDisplayName($this->user, $options['proper'], $options['tags']);
			if ($options['threadCount'] && $this->thread_count) {
				$name .= " ({$this->thread_count})";
			}
		}
		return $name;
	}

	public function getAuthorNameAttribute()
	{
		return $this->authorName();
	}

	public function getHasStaffAttribute()
	{
		return (bool) count($this->staff_recipients);
	}

	public function getHasClientAttribute()
	{
		return (bool) $this->client_recipient;
	}

	public function getClientStatusAttribute()
	{
		$user = null;
		if ($this->isClient($this->user)) {
			$user = $this->user;
		} elseif ($this->client_recipient) {
			$user = $this->client_recipient->user;
		}

		if ($user) {
			return (string) MessageService::getStatusRecord($this, $user);
		}
	}

	public function getBodyAttribute($value)
	{
		return Purifier::clean($value);
	}

	public function getTemplateOptions()
	{
		$templates = TemplateModel::all();
		$templateOptions = ['0' => 'None'];
		foreach ($templates as $template) {
			$templateOptions[(string) $template->id] = $template->name;
		}
		return $templateOptions;
	}

	public function filterFields($fields, $context = null)
	{
		$template = null;
		if (!empty($this->attributes['_template'])) {
			$template = TemplateModel::find(intval($this->attributes['_template']));
		}

		$subject = !empty($this->attributes['subject']) ? $this->attributes['subject'] : $this->subject;
		$body    = $this->body;

		if ($template) {
			$subject = empty(preg_replace('/\s+/', '', $subject)) ? $template->subject : $subject;
			$body    = empty(preg_replace('/\s+/', '', $body)) ? $template->body : $body;
		}

		if (isset($fields->subject)) {
			$fields->subject->value = $subject;
		}

		$this->body = $body;
	}


    /*
	 * Scopes
	 */

	public function scopeLatestReceivedGroupedByThread($query)
	{
		$groupedQuery = clone $query;
		// Get the message ids to display flattened by groups, get by oldest first because the last item added to the thread_id key returned by lists
	    // will be the only one in that array element.
	    $groupedMessageIds = array_values($groupedQuery->received()->removeOrder('sent_at')->oldest()->lists('id', 'thread_id'));

	    // Get all messages in the previously selected array of ids, sort by newest this time
	    return $query->whereIn('id', $groupedMessageIds)->removeOrder('sent_at')->newest();
	}

	public function scopeParticipantsInclude($query, $user = null)
	{
		return $query->where(function ($q) use ($user) {
			$q->whereHas('participants', function ($q2) use ($user) {
		        $q2->forUser($user, true);
	        })->orWhere(function ($q2) use ($user) {
		        $q2->forUser($user, true);
	        });
		});
	}

	public function scopeSiblings($query, $message = null, $user = null, $ignoreCurrent = true)
    {
		$message = $message ?: $this;
		if ($message->exists && $ignoreCurrent) {
			$query->notCurrent();
		}

		if ($message->thread_id) {
			$query->inThread($message->thread_id)
				->notStatus([Status::DRAFT, Status::ARCHIVED])
		    	->participantsInclude($user)
		    	->newest();
		} else {
			$query->where('id', 0);
		}

		return $query;
    }

    public function scopeOlderSiblings($query, $message = null, $user = null) {
	    $message = $message ?: $this;
	    $query->siblings($message, $user)->whereDate('sent_at', '<', $message->sent_at);
    }

    public function scopeNotCurrent($query, $message = null)
    {
	    $message = $message ?: $this;
	    if ($message->exists) {
		    return $query->where('id', '!=', $message->id);
	    } else {
		    return $query;
	    }
    }

    public function scopeInThread($query, $thread_id = null)
    {
	    $thread_id = $thread_id ?: $this->thread_id;
	    return $query->where('thread_id', '=', $thread_id);
    }

    /**
	 * Removes the provided orderby column rule from the QueryBuilder orders array to reset the orderBy for that column
	 *
	 * @param Builder $query
	 * @param string $orderByColumn
	 * @return Builder $query
	 */
    public function scopeRemoveOrder($query, $orderByColumn)
    {
	    // Orders property can have mixed up integer index, flatten the array to negate any weird keys in this array
	    $orders = $query->getQuery()->orders;
	    if (is_array($orders)) {
		    $orders = array_values($query->getQuery()->orders);
		    $i = 0;
		    foreach ($orders as $order) {
			    if ($order['column'] === $orderByColumn) {
				    unset($orders[$i]);
			    }
			    $i++;
		    }
		    $query->getQuery()->orders = $orders;
	    }
	    return $query;
    }

    public function scopeNewest($query)
    {
	    return $query->orderBy('sent_at', 'desc');
    }

    public function scopeOldest($query)
    {
	    return $query->orderBy('sent_at', 'asc');
    }

	public function scopeWithUserStatus($query, $user = null)
	{
		return $query->with(['status' => function ($q) use ($user) {
			$q->forUser($user, true);
		}]);
	}

    public function scopeStatus($query, $status, $user = null)
    {
        return $query->participantsInclude($user)
        	->whereHas('status', function ($q) use ($status, $user) {
	        	// NOTE: If $user is null or invalid, it will default to the current user
				$q->forUser($user, true);
				is_array($status) ? $q->whereIn('status', $status) : $q->where('status', $status);
        	});
    }

    public function scopeNotStatus($query, $status, $user = null)
    {
	    return $query->whereHas('status', function ($q) use ($status, $user) {
	        // NOTE: If $user is null or invalid, it will default to the current user
	        $q->forUser($user, true);
            is_array($status) ? $q->whereNotIn('status', $status) : $q->where('status', '!=', $status);
        });
    }

    public function scopeDraft($query, $user = null)
    {
        return $query->status(Status::DRAFT, $user);
    }

    public function scopeUnread($query, $user = null)
    {
        return $query->status(Status::UNREAD, $user);
    }

    public function scopeRead($query, $user = null)
    {
	    return $query->status(Status::READ, $user);
    }

    public function scopeReceived($query, $user = null)
    {
        return $query->notForUser($user, true)->status([Status::READ, Status::UNREAD], $user);
    }

    public function scopeSent($query, $user = null)
    {
	    return $query->forUser($user, true)->whereNotNull('sent_at');
    }

    public function scopeArchived($query, $user = null)
    {
	    return $query->status(Status::ARCHIVED, $user);
    }

    public function scopeHasAttachments($query)
    {
        return $query->has('attachments');
    }

    public function scopeNoAttachments($query)
    {
        return $query->doesntHave('attachments');
    }


    /*
	 * Message status functions
	 */

	public function userIsParticipant($user = null)
	{
		$user = static::defaultToCurrentUser($user);
		if (static::compareUsers($user, $this->user)) {
			return true;
		} else {
			foreach ($this->participants as $participant) {
				if (static::compareUsers($user, $participant->user)) {
					return true;
				}
			}
		}
		return false;
	}

	public function hasAttachments()
	{
		return (bool) count($this->attachments);
	}

	public function isDraft()
    {
	    return MessageService::checkStatus($this, $this->user, Status::DRAFT);
    }

    public function isUnread($user = null)
    {
	    return MessageService::checkStatus($this, $user, Status::UNREAD);
    }

    public function isRead($user = null)
    {
	    return MessageService::checkStatus($this, $user, Status::READ);
    }

    public function isSent()
    {
	    return (bool) $this->sent_at;
    }

    public function isArchived($user = null)
    {
	    return MessageService::checkStatus($this, $user, Status::ARCHIVED);
    }

	public function markDraft()
	{
		MessageService::markDraft($this);
	}

	public function markUnread($user = null)
	{
		MessageService::markUnread($this, $user);
	}

	public function markRead($user = null)
	{
		MessageService::markRead($this, $user);
	}

	public function archive($user = null)
	{
		MessageService::markArchived($this, $user);;

		if (!$this->isDraft()) {
			$siblings = $this->siblings()->get();
			if (!$siblings->isEmpty()) {
				foreach ($siblings as $message) {
					MessageService::markArchived($message, $user);
				}
			}
		}
	}

    public function restoreArchived($user = null)
    {
	    if ($this->isArchived($user)) {
		    MessageService::markRead($this, $user);
	    }

	    $siblings = $this->newQuery()
	    		->inThread($this->thread_id)
				->notStatus(Status::DRAFT)
		    	->participantsInclude($user)
		    	->get();
	    if (!$siblings->isEmpty()) {
		    foreach ($siblings as $message) {
			    if ($message->isArchived($user)) {
				    MessageService::markRead($message);
			    }
		    }
	    }
    }
}
