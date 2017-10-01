<?php namespace Look\Essentials\Components;

use Cms\Classes\ComponentBase;
use Look\Essentials\Classes\LookHelper;

class ChildPages extends ComponentBase
{
	/**
     * @var \RainLab\Pages\Components\StaticPage A reference to the static page component
     */
    public $staticPageComponent;
    
    /**
     * @var array of \RainLab\Pages\Classes\Page References to the child static page objects for the current page
     */
    public $childPageObjects;
    
    /**
     * @var array of child pages data
     */
    public $pages;
	
    public function componentDetails()
    {
        return [
            'name'        => 'Child Pages Listing Component',
            'description' => 'Displays a listing of the child pages of this page.'
        ];
    }
    
    public function onRun()
    {
        // Attempt to check if the staticPage component is attached to the rendering template
	    $this->staticPageComponent = $this->findComponentByName('staticPage');
	    if ($this->staticPageComponent->pageObject) {
		    // Attempt to load the child pages from the pageObject attached to this static page component
		    $this->childPageObjects = $this->staticPageComponent->pageObject->getChildren();
		    
		    // Loop through the child page objects and put together the array of child page information
		    foreach ($this->childPageObjects as $childPage) {
			    $viewBag = $childPage->settings['components']['viewBag'];
			    $this->pages[] = array(
				    'title'              => @$viewBag['title'],
				    'excerpt'            => LookHelper::trimByWords(@$childPage->attributes['markup'], 25),
				    'url'                => @$viewBag['url'],
				    'layout'             => @$viewBag['layout'],
				    'is_hidden'          => @$viewBag['is_hidden'],
				    'navigation_hidden'  => @$viewBag['navigation_hidden'],
				    'featuredImage'      => [
					    'image'    => @$viewBag['featuredImage'],
					    'size'     => @$viewBag['fiSize'],
					    'bgPosX'   => @$viewBag['fiBackgroundPositionY'],
					    'bgPosY'   => @$viewBag['fiBackgroundPositionX'],
					    'height'   => @$viewBag['fiHeight'],
					    'colour'   => @$viewBag['fiColour'],
					    'class'    => @$viewBag['fiClass'],
				    ],
			    );
		    }
		    
		    
// 		    dd($this->staticPageComponent->pageObject->getChildren());
		}
    }

}