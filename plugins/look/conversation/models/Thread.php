<?php namespace Look\Conversation\Models;

use Model;

/**
 * Class Thread
 */
class Thread extends Model
{
	use \October\Rain\Database\Traits\Validation;
	use \October\Rain\Database\Traits\Encryptable;
	
	/**
     * @var string The database table used by the model.
     */
    public $table = 'look_conversation_threads';
    
    public $fillable = [
	    'subject',
	    'forwarded_from',
    ];
    
    public $rules = [
	    'subject' => 'required',
    ];
    
    public $encryptable = [
	    'subject',
    ];
    
    /*
     * Relations
     */
   
    public $hasMany = [
	    'messages' => [
		    'Look\Conversation\Models\Message',
		    'key' => 'thread_id',
	    ],
    ];
}