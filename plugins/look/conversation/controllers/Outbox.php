<?php namespace Look\Conversation\Controllers;

use Backend;
use BackendMenu;
use Backend\Classes\Controller;

use Look\Conversation\Classes\MessageService;

/**
 * Class Outbox
 */
class Outbox extends Controller
{
	public $requiredPermissions = ['look.conversation.access_outbox'];
	
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Look.Conversation.Behaviors.MessagesController',
        'Look.Conversation.Behaviors.CheckedMessagesController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Look.Conversation', 'conversation', 'outbox');
    }

    public function listExtendQuery($query)
    {
        return $query->sent();
    }
    
    public function preview_onUnsend($id)
    {
	    $message = MessageService::getMessageById($id);
	    MessageService::unsendMessage($message);
	    // TODO: NOT SENDING
	    // TODO: Load indicator for button, make it popup or at least screen blocking load indicator and use it on frontend as well
		$this->flashMessage = 'look.conversation::lang.controllers.general.messages.unsent';
		$this->handleFlash();
		return Backend::redirect('look/conversation/drafts/update/' . $message->id);
    }
}