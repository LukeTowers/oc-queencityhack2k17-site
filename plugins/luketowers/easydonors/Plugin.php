<?php namespace LukeTowers\EasyDonors;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{
    
    /**
     * Returns information about this plugin.
     *
     * @return array
     */
    public function pluginDetails()
    {
        return [
            'name'        => 'luketowers.easydonors::lang.plugin.name',
            'description' => 'luketowers.easydonors::lang.plugin.description',
            'author'      => 'LukeTowers',
            'icon'        => 'icon-gift',
            'homepage'    => 'https://luketowers.ca',
        ];
    }
    
    
    /**
     * Registers any back-end permissions used by this plugin.
     *
     * @return array
     */
    public function registerPermissions()
    {
        return [
            'luketowers.easydonors.donors.view'        => ['tab' => 'luketowers.easydonors::lang.permissions.tab', 'label' => 'luketowers.easydonors::lang.permissions.donors.view'],
            'luketowers.easydonors.donors.manage'      => ['tab' => 'luketowers.easydonors::lang.permissions.tab', 'label' => 'luketowers.easydonors::lang.permissions.donors.manage'],
            'luketowers.easydonors.donations.view'     => ['tab' => 'luketowers.easydonors::lang.permissions.tab', 'label' => 'luketowers.easydonors::lang.permissions.donations.view'],
            'luketowers.easydonors.donations.manage'   => ['tab' => 'luketowers.easydonors::lang.permissions.tab', 'label' => 'luketowers.easydonors::lang.permissions.donations.manage'],
        ];
    }
    
    
    /**
     * Registers back-end navigation items for this plugin.
     *
     * @return array
     */
    public function registerNavigation()
    {
        return [
            'easydonors' => [
                'label'       => 'luketowers.easydonors::lang.navigation.easydonors.main_label',
                'url'         => Backend::url('luketowers/easydonors/donors'),
                'icon'        => 'icon-gift',
                'permissions' => ['luketowers.easydonors.*'],
                'order'       => 500,
                'sideMenu'    => [
	                'donors'             => [
		                'label'       => 'luketowers.easydonors::lang.navigation.easydonors.main_label',
		                'url'         => Backend::url('luketowers/easydonors/donors'),
		                'icon'        => 'icon-group',
		                'permissions' => ['luketowers.easydonors.donors.*'],
		            ],
		            'donations'          => [
		                'label'       => 'luketowers.easydonors::lang.navigation.easydonors.donations',
		                'url'         => Backend::url('luketowers/easydonors/donations'),
		                'icon'        => 'icon-money',
		                'permissions' => ['luketowers.easydonors.donations.*'],
		            ],
                ],
            ]
        ];
    }
    
     /**
     * Registers any front-end components implemented in this plugin.
     *
     * @return array
     */
    public function registerComponents()
    {
        return [
            'LukeTowers\EasyDonors\Components\DonationForm' => 'donationForm',
        ];
    }
}
