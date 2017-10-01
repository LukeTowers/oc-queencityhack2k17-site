<?php namespace Look\Conversation\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Class Drafts
 */
class Drafts extends Controller
{
	public $requiredPermissions = ['look.conversation.access_drafts'];
	
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
        'Look.Conversation.Behaviors.MessagesController',
        'Look.Conversation.Behaviors.CheckedMessagesController',
    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = '$/look/conversation/controllers/configs/relations.recipients.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Look.Conversation', 'conversation', 'drafts');
    }
    
    public function listExtendQuery($query)
    {
        return $query->draft();
    }
}