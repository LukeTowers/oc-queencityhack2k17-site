<?php namespace Look\Conversation\Components;

use Url;
use Lang;
use Flash;
use Backend;
use Cms\Classes\Page;
use Cms\Classes\Theme;
use Cms\Classes\ComponentBase;
use System\Classes\CombineAssets;

use Look\Conversation\Classes\MessageService;

use Look\Conversation\Models\Status as StatusModel;
use Look\Conversation\Models\Thread as ThreadModel;
use Look\Conversation\Models\Message as MessageModel;

/**
 * Class Conversation
 */
class Conversation extends ComponentBase
{
	/**
	 * The context that this component is operating under
	 * @var string
	 */
	public $context;

	/**
	 * The user's messages for the current context
	 * @var Collection
	 */
	public $messages;

	/**
	 * The main message model within the current context
	 * @var MessageModel
	 */
	public $message;

	/**
	 * The flash message that should be displayed before returning a response
	 * @var string
	 */
	protected $flashMessage;

	/**
	 * Flag for status of request, used for Flash message type
	 * @var bool
	 */
	protected $success;

	/**
	 * File uploader component
	 * @var array of Component(s)
	 */
	public $fileUploaders = [];


	/**
	 * Register the component's information
	 */
    public function componentDetails()
    {
        return [
            'name'        => 'Conversation Component',
            'description' => 'Provides interface for interacting with the conversation plugin data',
        ];
    }

    /**
	 * Register the component's properties
	 */
    public function defineProperties()
    {
        return [
	        'context' => [
                'title'       => 'Context',
                'description' => 'Context of the component',
                'default'     => '{{ :context }}',
                'type'        => 'string'
            ],
            'resourceId' => [
                'title'       => 'Resource ID',
                'description' => 'Resource ID to manage within the provided context',
                'default'     => '{{ :resourceId }}',
                'type'        => 'string'
            ],
            'defaultPage' => [
	            'title'       => 'Default CMS Page',
	            'description' => 'CMS Page that hosts this component, used for generating URLs in a "widget" context',
	            'default'     => 'app.messages/index',
	            'type'        => 'dropdown',
            ]
        ];
    }

    /**
	 * Get the options for the defaultPage property
	 */
    public function getDefaultPageOptions()
    {
	    $options = [];
		$pages = Page::listInTheme(Theme::getEditTheme());
		foreach ($pages as $page) {
			$options[$page->baseFileName] = "{$page->title} ({$page->url})";
		}
		return $options;
    }

    /**
	 * Initialize the component
	 */
    public function init()
    {
	    // Prepare the component variables
	    $this->prepareVars();

	    // Insert the component assets
	    $this->insertAssets();

	    // Hook into beforeRunAjaxHandler to run some custom AJAX handling logic
	    $this->bindEvent('component.beforeRunAjaxHandler', [$this, 'beforeAjax']);
    }

    /**
	 * Prepare the variables for this component
	 */
    public function prepareVars()
    {
	    $this->context = $context = $this->property('context');

	    // Prepare and attach the fileuploader components
	    $options = [];
	    $defaultOptions = [
		    'fileTypes'       => 'pdf,txt,text,csv,tsv,jpg,jpeg,bmp,png,gif,doc,docx,xls,xlsx,ppt,pptx',
		    'modelClass'      => '\Look\Conversation\Models\Message',
		    'modelKeyColumn'  => 'attachments',
	    ];

	    switch ($context) {
		    case 'create':
		    	$options['createAttachments'] = $defaultOptions;
		    	$options['createAttachments']['deferredBinding'] = true;
				break;
			case 'update':
				$options['updateAttachments'] = $defaultOptions;
				$message = $this->getRequestMessage('url', true);
				if ($message->exists) {
					$options['updateAttachments']['identifierValue'] = $message->id;
				} else {
					$options['updateAttachments']['deferredBinding'] = true;
				}
				break;
			case 'reply':
				$options['replyAttachments'] = $defaultOptions;
				$options['replyAttachments']['deferredBinding'] = true;
				break;
	    }

	    if (in_array($context, ['view', 'update', 'reply'])) {
		    // Attach the threading preview attachments
		    $options['viewAttachments'] = $defaultOptions;
		    if ($context === 'view') {
			    $message = $this->getRequestMessage();
		    } else {
			    $message = $this->getRequestMessage('ajax');
		    }

			if ($message->exists) {
				$options['viewAttachments']['identifierValue'] = $message->id;
			} else {
				$options['viewAttachments']['deferredBinding'] = true;
			}
	    }

	    // Attach fileUploaders
	    $fileUploaders = [];
	    foreach ($options as $alias => $config) {
		    $fileUploaders[$alias] = $this->addComponent(
			    'NetSTI\Uploader\Components\FileUploader',
				$alias,
				$config
		    );
	    }
	    $this->fileUploaders = $fileUploaders;
    }

    /**
	 * Attach any required assets
	 */
	public function insertAssets()
	{
		// Add frontend assets
		$this->addJs('assets/js/frontend.js');
		$this->addCss('assets/css/frontend.css');

		// Contexts that require Froala assets
		// TODO: either provide october js assets in the theme and include everywhere or include loader.js requirements on contexts without the richeditor but with buttons
		// Including by default on every page may make more sense for supporting those aspects of the AJAX api on every page on the site
		$requiresJs  = ['create', 'reply', 'update'];
		$requiresCss = ['view', 'create', 'reply', 'update'];

		if (in_array($this->context, $requiresJs)) {
			// Required CSS for the richeditor
			$this->addCss(Url::to(CombineAssets::combine(['oc-icons.less'], plugins_path('look/conversation/assets/less/'))));

			// Required JS for the richeditor
			$this->addJs('/modules/system/assets/ui/storm-min.js');
			$this->addJs('/plugins/look/conversation/assets/js/richeditor.js');
			$this->addJs('/modules/backend/formwidgets/richeditor/assets/js/build-min.js');
		}

		if (in_array($this->context, $requiresCss)) {
			$this->addCss('/modules/backend/formwidgets/richeditor/assets/css/richeditor.css');
		}
	}

    /**
	 * Get the message for the current request
     *
     * @param string $messageIdSource The source to look for the message id from. Accepts both sources if omitted. Options: ajax, url
     * @param bool $force If true, only accept the message id from the specified $messageIdSource instead of merely preferring the $messageIdSource
     * @return MessageModel $message
     */
    protected function getRequestMessage($messageIdSource = '', $force = false)
    {
	    $message   = new MessageModel();
	    $messageId = false;
	    // Default to AJAX source having higher priority
	    $messageIdSource = !empty($messageIdSource) ? $messageIdSource : 'ajax';
	    $source    = [
		    'url'  => intval($this->property('resourceId')),
		    'ajax' => intval(post('messageId')),
	    ];

	    if ($force) {
		    $messageId = $source[$messageIdSource];
	    } else {
		    switch ($messageIdSource) {
			    case 'url':
			    	$messageId = $source['url'] ? $source['url'] : $source['ajax'];
			    	break;
			    case 'ajax':
			    	$messageId = $source['ajax'] ? $source['ajax'] : $source['url'];
			    	break;
		    }
	    }

	    if ($messageId) {
		    if ($this->messages) {
			    $message = $this->messages->filter(function ($message) use ($messageId) {
					return $message->id === intval($messageId);
				})->first();
		    }

		    if (!$message->exists) {
			    $message = MessageService::getMessageById($messageId);
		    }
	    }

		if (!$message) {
			throw new \Exception("The requested message (ID: $messageId) could not be retrieved.");
		}
		return $message;
    }

    /**
	 * Get the session key for the current request
	 *
	 * @return string $sessionKey
	 */
	public function getSessionKey()
	{
		return post('_session_key');
	}

    /**
	 * Makes a URL to the current page that this component passing on the route params provided
	 *
	 * @param array $params
	 * @return string $url
	 */
    public function makeUrl(array $params = [])
    {
		if ($this->context === 'widget') {
			$pageFileName = $this->property('defaultPage');
		} else {
			$pageFileName = $this->controller->getPage()->baseFileName;
		}

		return $this->pageUrl($pageFileName, $params);
    }

    /**
	 * Makes a URL to the specified message
	 *
	 * @param MessageModel $message
	 * @return string $url
	 */
    public function makeMessageUrl($message)
    {
	    if ($message->isDraft()) {
		    $context = 'update';
	    } else {
		    $context = 'view';
	    }
	    return $this->makeUrl([
	    	'context' => $context,
	    	'resourceId' => $message->id
	    ]);
    }

    /**
	 * Makes a redirect response to the generated url via the provided route params
	 *
	 * @param string|MessageModel|array $value String = redirect to value provided, MessageModel = redirect to messageUrl for message, Array = $this->makeUrl for provided array
	 * @return RedirectResponse
	 */
    protected function makeRedirect($value = null)
    {
	    if ($value instanceof MessageModel && $value->exists) {
		    $url = $this->makeMessageUrl($value);
	    } elseif (is_string($value)) {
		    $url = $value;
	    } else {
		    $value = is_array($value) ? $value : [];
		    $url = $this->makeUrl($value);
	    }

	    return redirect($url);
    }

    /**
	 * Redirects to the latest message that isn't archived or a draft
	 *
	 * @param MessageModel $message
	 * @return RedirectResponse|null
	 */
	public function forceLatestMessage($message)
	{
		$newestInThread = $message->thread->messages()->notStatus([StatusModel::DRAFT, StatusModel::ARCHIVED])->newest()->first();
		if ($newestInThread && $newestInThread->id !== $message->id) {
			return $this->makeRedirect($newestInThread);
		}
	}

	/**
	 * Handle a save request by either returning a 'saved' indicator or sending the message and returning a 'sent' indicator
	 *
	 * @param MessageModel $message
	 * @return RedirectResponse|null
	 */
	protected function maybeSend($message)
	{
		$response = null;

		if (MessageService::isValidMessage($message)) {
		    if (post('send')) {
			    // If the send command was sent with the save request, then send it
				MessageService::sendMessage($message);
				$this->flashMessage = 'look.conversation::lang.controllers.general.messages.sent';

				// Redirect to the inbox
				$response = $this->makeRedirect(['context' => 'inbox']);
		    } else {
			    // Else this is just draft, update flash message
			    $this->flashMessage = 'look.conversation::lang.controllers.general.messages.saved';

			    // Redirect to the managing screen for that message
			    $response = $this->makeRedirect($message);
		    }

		    $this->success = true;
	    } else {
		    throw new \Exception('The provided message object was invalid');
	    }

	    return $response;
	}




    //
    // Action Methods
    //

    public function widget()
    {
	    // Load the messages
	    $this->messages = MessageModel::latestReceivedGroupedByThread()->get();
    }

    public function inbox()
    {
	    // Redirect to the base view if a resourceId is provided
	    if (!empty($this->property('resourceId'))) {
		    return $this->makeRedirect([
		    	'context' => 'inbox',
		    	'resourceId' => false,
		    ]);
	    }

	    // Load the messages
	    $this->messages = MessageModel::latestReceivedGroupedByThread()->get();
    }

    public function drafts()
    {
	    // Redirect to the base view if a resourceId is provided
	    if (!empty($this->property('resourceId'))) {
		    return $this->makeRedirect([
		    	'context' => 'drafts',
		    	'resourceId' => false,
		    ]);
	    }

		// Load the messages
	    $this->messages = MessageModel::draft()->orderBy('updated_at', 'desc')->get();
    }

    public function sent()
    {
	    // Redirect to the base view if a resourceId is provided
	    if (!empty($this->property('resourceId'))) {
		    return $this->makeRedirect([
		    	'context' => 'sent',
		    	'resourceId' => false,
		    ]);
	    }

	    // Load the messages
	    $this->messages = MessageModel::sent()->orderBy('sent_at', 'desc')->get();
    }

    public function create()
    {
	    // Stub
    }

    public function create_onSave()
    {
	    $message = new MessageModel();

	    $thread = ThreadModel::create(['subject' => post('subject')]);
	    $message->thread = $thread;
	    $message->body = post('body');
	    $message->save(null, $this->getSessionKey());

	    // Mark the message as a draft by default
	    MessageService::markDraft($message);

	    // Finish handling the save request
	    return $this->maybeSend($message);
    }

    public function reply()
    {
	    $this->message = $this->getRequestMessage();
    }

    public function reply_onSave()
    {
	    // Create the reply
	    $message = new MessageModel();
	    $message->body = post('body');
	    $message = MessageService::prepareReply($message, $this->getRequestMessage('url', true), $this->getSessionKey());
	    $message->save(null, $this->getSessionKey());

	    // Mark the message as a draft by default
	    MessageService::markDraft($message);

	    // Finish handling the save request
	    return $this->maybeSend($message);
    }

    public function update()
    {
	    $this->message = $this->getRequestMessage();
    }

    public function update_onSave()
    {
	    // Get the message for this request
	    $message = $this->getRequestMessage('url', true);

	    // Update the message
	    if ($message->siblings()->get()->isEmpty()) {
		    $message->subject = post('subject');
	    }
	    $message->body = post('body');
	    $message->save(null, $this->getSessionKey());

	    // Finish handling the save request
	    return $this->maybeSend($message);
    }

    public function view()
    {
        $message = $this->getRequestMessage();
        if ($message->isDraft()) {
            return $this->makeRedirect([
	            'context' => 'update',
	            'resourceId' => $message->id,
	        ]);
        } elseif ($message->isUnread()) {
            $message->markRead();
        }
        $redirect = $this->forceLatestMessage($message);
	    if ($redirect) { return $redirect; }

	    $this->message = $message;
    }



    //
    // Handlers
    //

    /**
	 * Run the action for the provided context
	 *
	 * @return mixed
	 */
    public function componentAction()
    {
	    $supportedContexts = ['inbox', 'drafts', 'sent', 'create', 'view', 'reply', 'update'];
		if (!in_array($this->context, $supportedContexts)) {
			return $this->makeRedirect(['context' => 'inbox']);
		}

		if ($this->methodExists($this->context)) {
			$response = $this->{$this->context}();
		}

		if ($response) {
			return $response;
		}
    }

	/**
	 * Main action method, called on every non-AJAX request that includes the component
	 */
	public function onRun()
	{
		$response = $this->componentAction();
		if ($response) {
			return $response;
		}
	}

	/**
	 * Prepares for an AJAX call
	 */
	public function beforeAjax($handler)
	{
		// Run the component action for the current context
		$this->componentAction();

		$action = $this->getContextHandlerMethod($handler);
		$action = $this->methodExists($action) ? $action : $handler; // Default to running the basic action instead if the context action doesn't exist
		$response = true;
		if ($this->methodExists($action)) {
			try {
				$response = $this->$action();
			} catch (\Exception $e) {
				$this->flashMessage = $e->getMessage();
				$this->success = false;
			}
		} else {
			$response = null;
		}

		if (!empty($this->flashMessage)) {
			$flashMessage = Lang::get($this->flashMessage);
			if ($this->success) {
				Flash::success($flashMessage);
			} else {
				Flash::error($flashMessage);
			}
		}

		if ($response) {
			return $response;
		}
	}

	protected function getContextHandlerMethod($handler)
	{
		return "{$this->context}_$handler";
	}

	/**
	 * Check to see if this method exists potentially including the context specific ajax handlers
	 *
	 * @param string $method The method to check for
	 * @param bool $ignoreContext Flag to disable checking within the specific context for the existance of the method
	 * @return bool
	 */
	public function methodExists($method, $ignoreContext = false)
	{
		$isContextAjax = false;
		if (!$ignoreContext) {
			if (substr($method, 0, 2) === 'on') {
				$isContextAjax = parent::methodExists($this->getContextHandlerMethod($method));
			}
		}

		return $isContextAjax || parent::methodExists($method);
	}

	/**
	 * Archive provided message
	 */
	public function onDelete()
    {
	    $message = $this->getRequestMessage();
	    if ($message->exists) {
		    $message->archive();
		    $this->flashMessage = 'look.conversation::lang.controllers.general.messages.deleted';
		    $this->success = true;
		    // TODO: Flash message not being respected
		    return $this->makeRedirect(['context' => 'inbox']);
	    } else {
		    throw new \Exception("The message you are trying to delete does not exist");
	    }
    }

    /**
	 * Display a message popup when clicking on an thread list item
	 */
	public function onClickMessageRow()
	{
		$this->message = $message = $this->getRequestMessage();

		if ($message->isUnread()) {
            $message->markRead();
        }

		// Initialize the fileUpload widget for this message to be rendered
		$messageContexts = ['create', 'view', 'reply', 'update'];
		if (in_array($this->context, $messageContexts)) {
			foreach ($this->fileUploaders as $fileUploader) {
				$fileUploader->onRun();
			}
		}

		return ['#message-container' => $this->renderPartial("@view-message")];
	}

	public function formatDate($message)
	{
		// TODO: Format like gmail with messages in same day, week, month, and default having different formats.
		$date = null;
		if (!empty($message->sent_at)) {
			$date = $message->sent_at;
		} else {
			$date = $message->updated_at;
		}

		return $date;
	}

	public function fromCurrentUser($message)
	{
		return MessageService::checkMessageOwner($message);
	}

	public function getClientRecipient()
	{
		return MessageService::getClientRecipient();
	}

    public function getScopeCount($scope)
    {
	    return MessageService::getScopeCount(null, $scope);
    }
}