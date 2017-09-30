<?php namespace LukeTowers\EasyDonors\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Donors Back-end Controller
 */
class Donors extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
    ];
    
    public $requiredPermissions = ['luketowers.easydonors.donors.*'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';
    public $relationConfig = 'config_relation.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('LukeTowers.EasyDonors', 'easydonors', 'donors');
    }
}