<?php namespace Look\Essentials\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\ComponentManager;

class LookEssentials extends ComponentBase
{

    public function componentDetails() {
        return [
            'name'        => "Look's Templating Essentials",
            'description' => "Provides access to Look's Essentials Templating components"
        ];
    }
    
    // Dynamically attach various Look Essentials components through this function
    public function init() {
		// Add the siteVariables component
	    $this->addComponent('Look\Essentials\Components\SiteVariables', 'siteVariables', array());
	    
/*
	    // Find all loaded components (framework wise not template wise)
	    $componentManager = ComponentManager::instance();
	    $loadedComponents = $componentManager->listComponents();
*/
	    
	    // Attempt to check if the staticPage component is attached to the rendering template
	    $staticPageComponent = $this->findComponentByName('staticPage');
	    if ($staticPageComponent) {
		    // Add the childPages component
			$this->addComponent('Look\Essentials\Components\ChildPages', 'childPages', array());
	    }
	}
}