<?php namespace NetSTI\Uploader;

use System\Classes\PluginBase;

class Plugin extends PluginBase
{

	// DETAILS
	public function pluginDetails(){
		return [
			'name' => 'FileUploader',
			'description' => 'Set a file uploader in frontend',
			'author' => 'NetSTI, Responsiv',
			'icon' => 'icon-upload',
			'homepage' => 'http://netsti.com/plugins',
		];
	}

	// COMPONENTS
	public function registerComponents()
	{
		return [
			'NetSTI\Uploader\Components\FileUploader'  => 'fileUploader',
			'NetSTI\Uploader\Components\ImageUploader' => 'imageUploader',
		];
	}
}
