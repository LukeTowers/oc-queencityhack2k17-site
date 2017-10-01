<?php namespace Look\Conversation;

use Backend;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;

/**
 * Class Plugin
 */
class Plugin extends PluginBase
{
    public $require = [
    	'RainLab.User',
    ];

    public function pluginDetails()
    {
        return [
            'name' => 'look.conversation::lang.plugin.name',
            'description' => 'look.conversation::lang.plugin.description',
            'author' => 'Look Agency',
            'icon' => 'icon-comments'
        ];
    }

    public function boot()
    {
        $this->injectAssets();
    }

    public function registerPermissions()
	{
	    return [
		    'look.conversation.access_inbox' => [
			    'label' => 'look.conversation::lang.permissions.access_inbox',
			    'tab'   => 'look.conversation::lang.permissions.tab',
		    ],
		    'look.conversation.access_outbox' => [
			    'label' => 'look.conversation::lang.permissions.access_outbox',
			    'tab'   => 'look.conversation::lang.permissions.tab',
		    ],
		    'look.conversation.access_drafts' => [
			    'label' => 'look.conversation::lang.permissions.access_drafts',
			    'tab'   => 'look.conversation::lang.permissions.tab',
		    ],
		    'look.conversation.access_archive' => [
			    'label' => 'look.conversation::lang.permissions.access_archive',
			    'tab'   => 'look.conversation::lang.permissions.tab',
		    ],
		    'look.conversation.manage_templates' => [
			    'label' => 'look.conversation::lang.permissions.manage_templates',
			    'tab'   => 'look.conversation::lang.permissions.tab',
		    ],
		    'look.conversation.manage_settings' => [
			    'label' => 'look.conversation::lang.permissions.manage_settings',
			    'tab'   => 'look.conversation::lang.permissions.tab',
		    ],
	    ];
	}

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'look.conversation::lang.settings.label',
                'description' => 'look.conversation::lang.settings.description',
                'category'    => 'look.conversation::lang.plugin.name',
                'icon'        => 'icon-comments',
                'class'       => 'Look\Conversation\Models\Settings',
                'order'       => 500,
                'keywords'    => 'look conversation message messaging default user',
                'permissions' => ['look.conversation.manage_settings']
            ]
        ];
    }

    public function registerNavigation()
    {
        return [
            'conversation' => [
                'label' => 'look.conversation::lang.navigation.messages',
                'url' => Backend::url('look/conversation/inbox'),
                'icon' => 'icon-comments',
                'permissions' => ['look.conversation.*'],
                'order' => 500,
                'sideMenu' => [
                    'inbox' => [
                        'label' => 'look.conversation::lang.navigation.inbox',
                        'icon' => 'icon-inbox',
                        'url' => Backend::url('look/conversation/inbox'),
                        'counter' => ['Look\Conversation\Classes\MessageService', 'getScopeCount'],
                        'counterLabel' => 'look.conversation::lang.filter.not_read',
                        'permissions' => ['look.conversation.access_inbox'],
                    ],
                    'outbox' => [
                        'label' => 'look.conversation::lang.navigation.outbox',
                        'icon' => 'icon-paper-plane',
                        'url' => Backend::url('look/conversation/outbox'),
                        'permissions' => ['look.conversation.access_outbox'],
                    ],
                    'drafts' => [
                        'label' => 'look.conversation::lang.navigation.drafts',
                        'icon' => 'icon-file-text-o',
                        'url' => Backend::url('look/conversation/drafts'),
                        'permissions' => ['look.conversation.access_drafts'],
                    ],
                    'archive' => [
                        'label' => 'look.conversation::lang.navigation.archive',
                        'icon' => 'icon-archive',
                        'url' => Backend::url('look/conversation/archive'),
                        'permissions' => ['look.conversation.access_archive'],
                    ],
                    'templates' => [
	                    'label' => 'look.conversation::lang.navigation.templates',
	                    'icon'  => 'icon-newspaper-o',
	                    'url'   => Backend::url('look/conversation/templates'),
	                    'permissions' => ['look.conversation.manage_templates'],
                    ],
                ],
            ],
        ];
    }

    public function registerComponents()
    {
        return [
	        'Look\Conversation\Components\Conversation' => 'conversation',
        ];
    }

    protected function injectAssets()
    {
        // Extend base controller to simulate push event and display unread messages count,
        // even when backend user is viewing different part of backend area
        \Backend\Classes\Controller::extend(function ($controller) {
            $controller->addCss('/plugins/look/conversation/assets/css/main.css');
            $controller->addJs('/plugins/look/conversation/assets/js/main.js');
        });
    }
}