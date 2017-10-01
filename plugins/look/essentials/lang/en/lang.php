<?php return [
    'plugin' => [
        'name'        => 'Look Essentials',
        'description' => 'Helper plugin mostly used for template building'
    ],
    'components' => [
	    'featured_image' => [
		    'name'        =>  'Featured Image',
		    'description' =>  'Displays the featured image for the record utilizing it\'s specified options',
	    ],
    ],
    'permissions' => [
        'tab' => 'Look Essentials',
    ],
    'navigation' => [
    ],
    'controllers' => [
	    'tabs'  => [
		    'edit'           => 'Edit',
		    'manage'         => 'Manage',
		    'featured_image' => 'Featured Image',
	    ],
	    'forms' => [
		    'return_to_prefix' => 'Return to ', // Translation to RTL languages will need to be fixed in places that use prefixing for development speed
		    'close_confirm'    => 'Changes you have made may not be saved',
		    'delete_confirm'   => 'Are you sure?',
		    'new_item'         => 'New',
		    'plural_delete_successful' => 'Successfully deleted those items',
	    ],
	    'instructions' => [
		    'featured_image' => [
			    'model_must_exist_first' => 'The :record must be created before you can attach a featured image',
		    ],
	    ],
    ],
    'models' => [
    ],
];