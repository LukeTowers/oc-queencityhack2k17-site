<?php namespace TeamAte\Ccrezqs\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class Dogs extends Controller
{
    public $implement = [
      'Backend\Behaviors\ListController',
      'Backend\Behaviors\FormController',
      'Backend.Behaviors.RelationController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('TeamAte.Ccrezqs', 'dogs');
    }


}
