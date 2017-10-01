<?php namespace Look\Conversation\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Templates Back-end Controller
 */
class Templates extends Controller
{
	public $requiredPermissions = ['look.conversation.manage_templates'];
	
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Look.Conversation', 'conversation', 'templates');
        $this->addJs('/plugins/look/conversation/assets/js/richeditor.js');
    }
}
