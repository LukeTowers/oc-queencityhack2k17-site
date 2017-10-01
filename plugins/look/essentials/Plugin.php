<?php namespace Look\Essentials;

use App;
use Config;
use Backend;
use System\Classes\PluginBase;
use Illuminate\Foundation\AliasLoader;

use Look\Essentials\Classes\DateFormat;
use Look\Essentials\Models\FeaturedImage as FeaturedImageModel;
use RainLab\Blog\Models\Post as PostModel;
use RainLab\Blog\Controllers\Posts as PostsController;

/**
 * TODO:
 * - Create date formatting settings for different use cases (long format, short format - for varying levels of specifity)
 * - Create the FeaturedImage code that will be able to manage featured images for static
 * pages, blog posts, any custom models. It will also have a default view with a lot of options
 * for displaying the component
 * - Contact settings
 */

/**
 * Essentials Plugin Information File
 */
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
            'name'        => 'look.essentials::lang.plugin.name',
            'description' => 'look.essentials::lang.plugin.description',
            'author'      => 'Look Agency',
            'icon'        => 'icon-leaf'
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
	        'Look\Essentials\Components\LookEssentials' => 'lookEssentials',
            'Look\Essentials\Components\SiteVariables'  => 'siteVariables',
            'Look\Essentials\Components\ChildPages'     => 'childPages',
            'Look\Essentials\Components\UserSettings'   => 'userSettings',
        ];
    }

    /**
     * Registers any front-end RainLab.Pages snippets implemented in this plugin.
     *
     * @return array
     */
    public function registerPageSnippets()
	{
	    return [
	        'Look\Essentials\Components\ChildPages'     => 'childPages',
	    ];
	}

    public function registerMarkupTags() {
	    return [
		    'filters' => [
			    'trimByWords'  => ['Look\Essentials\Classes\LookHelper', 'trimByWords'],
			    'slugify'      => 'str_slug',
			    'media'        => ['Cms\Classes\MediaLibrary', 'url'],
		    ],
		    'functions' => [
			    'trans'        => ['Lang', 'get'],
		    ],
	    ];
    }


    public function boot()
	{
		// Extend the RainLab.Blog plugin
		$this->extendRainLabBlog();

		// Extend the RainLab.User plugin
		$this->extendRainLabUser();

		// Add date formatting helper
		App::singleton('dateFormat', function() {
			return new DateFormat;
		});
		
		// Setup required packages
		$this->bootPackages();
	}
	
	/**
	 * Boots (configures and registers) any packages found within this plugin's packages configuration value
	 *
	 * @see https://luketowers.ca/blog/how-to-use-laravel-packages-in-october-plugins
	 * @author Luke Towers <octobercms@luketowers.ca>
	 */
	public function bootPackages()
	{
		// Get the namespace of the current plugin to use in accessing the Config of the plugin
		$pluginNamespace = str_replace('\\', '.', strtolower(__NAMESPACE__));
		
		// Instantiate the AliasLoader for any aliases that will be loaded
		$aliasLoader = AliasLoader::getInstance();
		
		// Get the packages to boot
		$packages = Config::get($pluginNamespace . '::packages');
		
		// Boot each package
		foreach ($packages as $name => $options) {
			// Setup the configuration for the package, pulling from this plugin's config
			if (!empty($options['config'] && !empty($options['config_namespace']))) {
				Config::set($options['config_namespace'], $options['config']);
			}
			
			// Register any Service Providers for the package
			if (!empty($options['providers'])) {
				foreach ($options['providers'] as $provider) {
					App::register($provider);
				}
			}
			
			// Register any Aliases for the package
			if (!empty($options['aliases'])) {
				foreach ($options['aliases'] as $alias => $path) {
					$aliasLoader->alias($alias, $path);
				}
			}
		}
	}

	public function extendRainLabUser() {
		\Cms\Classes\CmsController::extend(function ($controller) {
			$controller->middleware('\Look\Essentials\Classes\AppAuthMiddleware');
		});
	}


	public function extendRainLabBlog() {
		if (class_exists('RainLab\Blog\Models\Post')) {
			// Extend the Post model to support featured images
		    PostModel::extend(function ($model) {
				// Setup the relationship with the FeaturedImage Model
			    $model->morphOne['featuredImage'] = ['Look\Essentials\Models\FeaturedImage', 'name' => 'owner'];

			    // Attach a custom deletion handler to remove attached featuredImages when the originating post is deleted
			    $model->bindEvent('model.beforeDelete', function() use ($model) {
					$featuredImage = FeaturedImageModel::getFromOwner($model);
					$featuredImage->delete();
			    });

			    // Attach a custom save handler to save the rich text area to the content_html column in the db as well as the content
			    $model->bindEvent('model.beforeSave', function() use ($model) {
				    $model->content_html = $model->content;
			    }, 100);
		    });
		}

		if (class_exists('RainLab\Blog\Controllers\Posts')) {
			// Extend the Posts controller to support the featured images custom fields
			// https://octobercms.com/docs/backend/forms#extend-form-fields
			PostsController::extendFormFields(function($form, $model, $context) {
				// Only run on PostModel $models that exist
				if (!$model instanceof PostModel) {
					// Exit before modifying
					return;
				}

				// Remove the default featured_images field
				$form->removeField('featured_images');

				// Replace the content field with a richeditor
				$form->addSecondaryTabFields([
					'content'     => [
						'tab'      => 'rainlab.blog::lang.post.tab_edit',
						'type'     => 'richeditor',
						'cssClass' => 'field-slim blog-post-preview',
						'stretch'  => true,
						'span'     => 'full',
						'size'     => 'large',
					]
				]);


				// Add instructions to save post before adding featured image
				if ($context === 'create') {
					$form->addSecondaryTabFields([
						'featuredImage' => [
							'type'  => 'hint',
							'path'  => '$/look/essentials/partials/field.instructions.featuredImage.htm',
							'label' => 'look.essentials::lang.controllers.tabs.featured_image',
							'tab'   => 'look.essentials::lang.controllers.tabs.featured_image',
							'span'  => 'full',
						],
					]);
				}

				// Only add in the featuredImage fields when the model record for the current post already exists
				// i.e. update view instead of create because we'll get "Call to a member function hasRelation() on
				// null on line 56 of modules/backend/traits/formmodelsaver.php " error if the model doesn't have an
				// entry in the database before attaching the featuredImage relation to it
				// TODO: Create some workaround so that we can have the fields present for an create view as well
				if ($model->exists) {
					FeaturedImageModel::getFromOwner($model);

					// Add the new secondary tab fields
					$form->addSecondaryTabFields([
						'featuredImage[path]'                => [
							'label'  => 'Image',
							'tab'    => 'look.essentials::lang.controllers.tabs.featured_image',
							'comment'=> 'Image to be displayed',
							'type'   => 'mediafinder',
							'mode'   => 'file',
							'span'   => 'left',
						],
						'featuredImage[options][fiSize]'     => [
							'label'  => 'Size',
							'comment'=> 'cover = crop, contain = fit, full = Full size stretched to fit',
							'tab'    => 'look.essentials::lang.controllers.tabs.featured_image',
							'type'   => 'text',
							'span'   => 'right',
						],
						'featuredImage[options][fiBGPosY]'   => [
							'label'  => 'Vertical Positioning',
							'comment'=> 'Default: center',
							'tab'    => 'look.essentials::lang.controllers.tabs.featured_image',
							'type'   => 'text',
							'span'   => 'left',
						],
						'featuredImage[options][fiBGPosX]'   => [
							'label'  => 'Horizontal Positioning',
							'comment'=> 'Default: center',
							'tab'    => 'look.essentials::lang.controllers.tabs.featured_image',
							'type'   => 'text',
							'span'   => 'right',
						],
						'featuredImage[options][fiHeight]'   => [
							'label'  => 'Height',
							'tab'    => 'look.essentials::lang.controllers.tabs.featured_image',
							'type'   => 'text',
							'span'   => 'left',
						],
						'featuredImage[options][fiBGColour]' => [
							'label'  => 'Background Colour',
							'tab'    => 'look.essentials::lang.controllers.tabs.featured_image',
							'type'   => 'colorpicker',
							'span'   => 'right',
						],
					]);
				}
			});
		}
	}
}