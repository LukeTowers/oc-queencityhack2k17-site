<?php namespace Look\Conversation\Models;

use Model;

/**
 * Model
 */
class Template extends Model
{
    use \October\Rain\Database\Traits\Validation;
    use \October\Rain\Database\Traits\SoftDelete;
    
    /**
     * @var string The database table used by the model.
     */
    public $table = 'look_conversation_templates';

    /**
     * Validation Rules
     * @var array $rules
     */
    public $rules = [
	    'name' => 'required',
    ];
    
    /**
	 * Attributes to be cast to JSON
	 * @var array $jsonable
	 */
	public $jsonable = [
		'data',
	];
	
	protected $dates = ['created_at', 'updated_at', 'deleted_at'];    
}