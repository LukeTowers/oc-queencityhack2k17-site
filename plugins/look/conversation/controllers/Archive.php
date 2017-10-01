<?php namespace Look\Conversation\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Class Archive
 */
class Archive extends Controller
{
	public $requiredPermissions = ['look.conversation.access_archive'];
	
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

        BackendMenu::setContext('Look.Conversation', 'conversation', 'archive');
    }

    public function listExtendQuery($query)
    {
        return $query->archived();
    }
}