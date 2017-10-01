<?php namespace Look\Conversation\Models;

use Model;

use RainLab\User\Models\User as UserModel;

/**
 * Class Participant
 */
class Participant extends Model
{
	use \October\Rain\Database\Traits\Validation;
	use \Look\Conversation\Traits\PolymorphicUserScopes;
	
	/**
     * @var string The database table used by the model.
     */
    public $table = 'look_conversation_participants';
    
    
    public $fillable = ['message_id', 'user_id', 'user_type'];
    
    public $rules = [
	    'user_id' => 'required',
    ];
    
    public $attributeNames = [
	    'user_id' => 'look.casefiles::lang.models.user.label',
    ];
    
    public $timestamps = false;
    
    
    /*
     * Relations
     */
   
    public $belongsTo = [
	    'message' => [
		    'Look\Conversation\Models\Message',
		    'key' => 'message_id',
	    ],
	    'client' => [
		    UserModel::class,
		    'key'   => 'user_id',
	    ],
    ];
    
    public $morphTo = [
	    'user' => [],
    ];
    
    
    public function beforeSave()
    {
	    // Support adding clients by user id only
	    if (!empty($this->user_id) && empty($this->user_type)) {
		    // TODO: Look into additional checks to ensure that this is only run when adding a client recipient from the message screen
		    $this->user_type = UserModel::class;
	    }
    }	
}