<?php namespace Look\Conversation\Behaviors;

use Lang;
use Flash;
use Backend;
use Backend\Classes\ControllerBehavior;
use October\Rain\Exception\ApplicationException;

use Look\Conversation\Classes\MessageService;

use Look\Conversation\Models\Status as StatusModel;
use Look\Conversation\Models\Thread as ThreadModel;
use Look\Conversation\Models\Message as MessageModel;

/**
 * Class MessagesController
 */
class MessagesController extends ControllerBehavior
{
	// Actually, just filter that from the onSave. No views will display unless they're setup, and filtering on save will prevent the posts to save data from incorrect contexts	
	
	// TODO: Proper threading and passing data from parent message onto forwarded emails
	// TODO: Replies, to field needs to contain all recipients prefixed with [Staff] or [Client] that the message will be going to
	// TODO: Forwards need to include option to include attachments (checked by default)
	
	/**
	 * Flag for having handled the request already
	 * @var bool
	 */
	protected $handled;
	
	/**
	 * The flash message that should be displayed before returning a response
	 * @var string
	 */
	public $flashMessage;
	
	/**
	 * Holds the newly created message object when message is saved as a draft instead of being sent immediately
	 * @var MessageModel
	 */
	protected $message;
	
	/**
	 * The current context of the call to this controller
	 * @var string
	 */
	protected $context;
	
	/**
     * Behavior constructor
     * @param Backend\Classes\Controller $controller
     */
    public function __construct($controller)
    {
        parent::__construct($controller);

        $this->controller->addJs('/plugins/look/conversation/assets/js/richeditor.js');
    }
	
	
	/**
	 * Controller Methods to handle
	 */
	
	public function create_onSave()
	{
		$this->context = 'create';
		return $this->processSave();
	}
	
	public function update_onSave($id)
	{
		$this->context = 'update';
		return $this->processSave($id);
	}
	
	public function update_onArchiveSingle($id)
	{
		$this->context = 'update';
		return $this->processArchival($id);
	}
	
	public function reply($id, $context = 'reply')
    {
	    $this->context = $context;
	    $redirect = $this->forceLatestMessage($id);
	    if ($redirect && !$this->messagePopup) { return $redirect; }
        return $this->formController()->create($context);
    }
	
	public function reply_onSave()
	{
		$this->context = 'reply';
		return $this->processSave();
	}
	
	public function forward($id, $context = 'forward')
    {
	    $this->context = $context;
        return $this->formController()->create($context);
    }
	
	public function forward_onSave()
	{
		$this->context = 'forward';
		return $this->processSave();
	}
	
	public function preview($id)
    {
        try {
            $message = $this->controller->formFindModelObject($id);
            if ($message->isDraft()) {
	            return Backend::redirect('look/conversation/drafts/update/' . $message->id);
            } elseif ($message->isUnread()) {	            
	            $message->markRead();
            }
            $this->context = 'preview';
            $redirect = $this->forceLatestMessage($message->id);
		    if ($redirect && !$this->messagePopup) { return $redirect; }            
        } catch (ApplicationException $e) {
            $this->controller->handleError($e);
        }

        return $this->formController()->preview($id);
    }
    
    public function preview_onArchiveSingle($id)
    {
	    $this->context = 'preview';
		return $this->processArchival($id);
    }
    
    public function preview_onRestoreSingle($id)
    {
	    $this->context = 'preview';
	    return $this->processRestoral($id);
    }
	
	
	/**
	 * Redirects to the latest message in the current controller / context combo if it the current combo is one of the selected
	 * Selected include: [inbox|outbox] / [preview|reply]
	 *
	 * @param int $messageId
	 * @return RedirectResponse|null
	 */
	public function forceLatestMessage($messageId)
	{
		if (get_class($this->controller) === 'Look\Conversation\Controllers\Inbox') {
			if (in_array($this->context, ['preview', 'reply'])) {
				$message = MessageService::getMessageById($messageId);
				$newestInThread = $message->thread->messages()->notStatus([StatusModel::DRAFT, StatusModel::ARCHIVED])->newest()->first();
				if ($newestInThread && $newestInThread->id !== $message->id) {
					return Backend::redirect("look/conversation/inbox/{$this->context}/{$newestInThread->id}");
				}
			}
		}
	}
	
	
	/**
	 * Main Logic
	 */
	public $threadListWidget;
	public function initThreadList($message, $user = null)
	{
		$listConfig = $this->makeConfig('$/look/conversation/controllers/configs/list.thread.yaml');
		$columnConfig = $this->makeConfig($listConfig->list);
		$columnConfig->model = $message;
		$columnConfig->alias = 'ThreadList';
		
		/*
         * Prepare the columns configuration
         */
        $configFieldsToTransfer = [
            'recordUrl',
            'recordOnClick',
            'recordsPerPage',
            'noRecordsMessage',
            'defaultSort',
            'showSorting',
            'showSetup',
            'showCheckboxes',
            'showTree',
            'treeExpanded',
            'customViewPath',
        ];

        foreach ($configFieldsToTransfer as $field) {
            if (isset($listConfig->{$field})) {
                $columnConfig->{$field} = $listConfig->{$field};
            }
        }
        
        $columnConfig->recordOnClick = "$.oc.threadBehavior.clickMessage(':id')";
        
        $widget = $this->makeWidget('Backend\Widgets\Lists', $columnConfig);
        $widget->addFilter(function ($query) use ($message, $user) {
	        return $query->siblings($message, $user);
        });
        $widget->bindEvent('list.extendColumns', function () use ($widget) {
	        // TODO: IMPORTANT: Reorder final columns array to put columns in correct order (atts, preview, from, to, created_at - improve this interface in general.
	        // TODO: Need to redirect any message that isn't the most recent in the thread to the most recent and provide a popup model for previewing other messages in the
	        // 		thread
	        $unhideColumns = ['from', 'recipientNames'];
	        $replacementColumns = [];
	        foreach ($unhideColumns as $unhide) {
		        $replacementColumns[$unhide] = $widget->columns[$unhide];
		        $replacementColumns[$unhide]['invisible'] = false;
		        $widget->removeColumn($unhide);
	        }
	        
	        $widget->removeColumn('subject_with_thread_count');
	        
	        $widget->addColumns($replacementColumns);
        });
        
        $this->threadListWidget = $widget;
        $this->addJs('/plugins/look/conversation/assets/js/threads.js');
	}
	
	protected $messagePopup;
	public function onThreadClickMessage()
	{
		$messageId = post('messageId');
		$this->messagePopup = true; // signal that the context is a message popup
		$this->controller->preview($messageId);
		
		return $this->makePartial('$/look/conversation/controllers/partials/popup.message.htm');
	}
	 
	 
	
	// Accessor for the formController behavior
	protected function formController()
    {
        return $this->controller->asExtension('FormController');
    }
    
    // Extend the form fields for shitty version of conversations
    public function formExtendFields($widget, $fields)
    {
	    $message = $widget->data ?: new MessageModel();
	    $parentMessage = new MessageModel();
	    
	    if ($this->messagePopup) {
		    $allowedFields = ['authorName', 'recipientNames', 'body', 'attachments'];
			foreach ($fields as $key => $field) {
				if (in_array($key, $allowedFields)) {
					$field->hidden = false;
				} else {
					unset($fields[$key]);
					$widget->removeField($key);
				}
			}
	    } else {
		    // If staff participants exist, then set the checkbox to true by default to feature this fact
		    if (MessageService::isValidMessage($message) && !empty($fields['_add_staff_recipients']) && $message->hasStaff) {
			    $fields['_add_staff_recipients']->value = true;
		    }
		    
		    // Get the parent message from the URL segment if within one of the supported contexts
		    if (in_array($widget->context, ['reply', 'forward'])) {
			    $parentMessage = $this->getOriginatingMessage();
		    }
		    		    
		    // Prepopulate relevant fields with the required data for reply and forward contexts
		    switch ($widget->context) {
			    case 'preview':
			    	$fields['recipientNames']->hidden = false;
			    	break;
			    case 'reply':
			    	$message = MessageService::prepareReply(
						$message,
						$parentMessage,
						$this->formController()->formGetSessionKey(),
						null,
						true
					);

			    	$fields['subject']->disabled     = true;
			    	$fields['recipientNames']->value = MessageService::getParticipantsNames(
			    											MessageService::getReplyParticipants(
			    												$message,
			    												$parentMessage,
			    												$this->formController()->formGetSessionKey()
			    											)
			    										);
			    	$fields['recipientNames']->span  = 'full';
			    	$fields['recipientNames']->hidden = false;
					break;
				case 'update':
					if (!empty($message->thread->forwarded_from)) {
						$this->disableClientRecipientField($widget, $fields);
					}
					break;
				case 'forward':
					$this->disableClientRecipientField($widget, $fields);
					
					$message = MessageService::prepareForwardedMessage($parentMessage, $message, $widget->getSessionKey());
					$fields['subject']->value = MessageService::generateForwardSubject($parentMessage);
					break;
		    }
		    
		    // Disable subject field on update contexts where the message thread has more than one message
		    if ($widget->context === 'update' && (count($message->thread->messages) !== 1)) {
			    $fields['subject']->disabled = true;
			    $fields['recipientNames']->hidden = false;
			    $fields['recipientNames']->span = 'full';
			    $fields['client_recipient']->hidden = true;
			    $fields['staff_recipients']->hidden = true;
			    $fields['_add_staff_recipients']->hidden = true;
		    }
		    
		    $addThreadList = false;
		    if ($widget->context !== 'forward') {
			    if ($message->exists || (($widget->context === 'reply') && $parentMessage->exists)) {
				    $this->initThreadList($message);
				    $addThreadList = true;
			    } elseif ($parentMessage->exists) {
				    $this->initThreadList($parentMessage);
				    $addThreadList = true;
			    }
		    }		    
		    
		    if ($addThreadList) {   
			    $widget->addFields([
				    '_thread' => [
					    'label' => 'look.conversation::lang.models.thread.label',
					    'type'  => 'partial',
					    'path'  => '$/look/conversation/controllers/partials/list.thread.htm',
				    ],
			    ]);
			    
			    $fields = array_merge(['_thread' => $widget->getField('_thread')], $fields);
		    }
	    }
	    
	    $widget->fields = $fields;
    }
    
    
    protected function disableClientRecipientField($widget, &$fields)
    {
	    $widget->removeField('_add_staff_recipients');
		$widget->removeField('client_recipient');
		$fields['staff_recipients']->span = 'full';
		$fields['staff_recipients']->trigger = null;
		unset($fields['staff_recipients']->config['trigger']);
    }

	// Process sending / saving messages
	public function formAfterSave($message)
	{	
		// Validate the provided model
		if (MessageService::isValidMessage($message)) {
			if (post('send')) {
				// If the send command was sent with the save request, then send the message
				MessageService::sendMessage($message);
				
				$this->flashMessage = 'look.conversation::lang.controllers.general.messages.sent';
				$this->handled = true;
			} else {
				// Mark the message as a draft by default
				MessageService::markDraft($message);
				$this->message = $message;
				
				$this->flashMessage = 'look.conversation::lang.controllers.general.messages.saved';
				$this->handled = true;
			}
		}
	}
	
	// Handle replies
	public function formBeforeSave($message)
	{
		if ($this->context === 'reply') {
			MessageService::prepareReply(
				$message,
				$this->getOriginatingMessage(),
				$this->formController()->formGetSessionKey()
			);
		}
		
		if ($this->context === 'forward') {
			$thread = new ThreadModel([
				'subject'        => $message->attributes['subject'],
				'forwarded_from' => $this->getOriginatingMessage()->thread_id,
			]);
			$thread->save();
			$message->thread = $thread;
		}
	}
	
	protected function getOriginatingMessage()
	{
		return MessageService::getMessageById(request()->segment(6));
	}
	
	// This runs the formController onSave() method and then returns the appropriate response
	// Used to return a custom response even though our main place for sending logic is the formAfterSave method
	protected function processSave($id = null)
	{
		$createContexts = ['create', 'reply', 'forward'];
		$updateContexts = ['update'];
		
		$response = null;
		$defaultResponse = null;
		if (in_array($this->context, $createContexts)) {
			$requestResponse = $this->formController()->create_onSave($this->context);
			if ($this->message) {
				$response = Backend::redirect('look/conversation/drafts/update/' . $this->message->id);
			} else {
				$response = Backend::redirect('look/conversation/inbox');
			}
		} elseif (in_array($this->context, $updateContexts) && $id) {
			$requestResponse = $this->formController()->update_onSave($id);
			if ($this->message) {
				$response = Backend::redirect('look/conversation/drafts/update/' . $this->message->id);
			}
		}
		
		$this->handleFlash();
		
		return $response ?: $requestResponse;
	}
	
	protected function processArchival($id)
	{
		$message = MessageService::getMessageById($id);
		if ($message->exists) {
			$message->archive();
			// TODO: Flash message not flashing
			$this->flashMessage = 'look.conversation::lang.controllers.general.archived';
			$this->handleFlash();
			return Backend::redirect('look/conversation/inbox');
		}
	}
	
	protected function processRestoral($id)
	{
		$message = MessageService::getMessageById($id);
		if ($message->exists) {
			$message->restoreArchived();
			// TODO: Flash message not flashing
			$this->flashMessage = 'look.conversation::lang.controllers.general.messages.restored';
			$this->handleFlash();
			return Backend::redirect('look/conversation/inbox/preview/' . $message->id);
		}
	}
	
	
	public function handleFlash() {
		if ($this->handled) {
			if (!empty($this->flashMessage)) {
				Flash::forget();
				Flash::success(Lang::get($this->flashMessage));
			}
		}
	}
}