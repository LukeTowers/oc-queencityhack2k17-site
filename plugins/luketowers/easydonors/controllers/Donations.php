<?php namespace LukeTowers\EasyDonors\Controllers;

use BackendMenu;
use Backend\Classes\Controller;

/**
 * Donations Back-end Controller
 */
class Donations extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];
    
    public $requiredPermissions = ['luketowers.easydonors.donors.*'];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('LukeTowers.EasyDonors', 'easydonors', 'donations');
    }
}