<?php namespace Look\Essentials\Components;

use Cms\Classes\ComponentBase;
use Backend\Models\BrandSetting;

class SiteVariables extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'Site Variables',
            'description' => 'Provides access to key site data.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
    
    public function onRun() {
	    $this->page['app_name']        = BrandSetting::get('app_name');
	    $this->page['app_description'] = BrandSetting::get('app_tagline');
    }
}