<?php namespace Look\Essentials\Models;

use Model;

/**
 * FeaturedImage Model
 */
class FeaturedImage extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'look_essentials_featured_images';
    
    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
    ];
    
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];
	
	/**
     * Relations
     * Defines the prefix for the relation
     * PREFIX_type is the fully qualified class name and PREFIX_id is the relating record's own id
     */
    public $morphTo = [
        'owner' => []
    ];
    
    /**
     * Helper function to retrieve a featuredImage model for a given owning model
     * Creates the featuredImage model if it doesn't exist.
     * NOTE: If not using polymorphic relationships, don't forget to set the relationship both ways
     *
     * @param Model $owner
     */
    public static function getFromOwner($owner) {
	    if ($owner->featuredImage) {
		    return $owner->featuredImage;
	    } else {
		     // Create a new record for the passed owner model
		    $featuredImage = new static;
		    
		    // Setup the relations from the featuredImage record to the passed owner record 
		    // (sets owner_id = $owner->id and owner_type = get_class($owner)
		    $featuredImage->owner = $owner;
		    
/*
		    // Initialize the options attribute to an empty array
		    // TODO: Remove once db setup with nullable correctly
		    $featuredImage->options = array();
		    $featuredImage->path = '';
*/
		    
		    // Save the new record
		    $featuredImage->save();
		    
		    // Return the newly created featuredImage
		    return $featuredImage;
	    }
    }
}