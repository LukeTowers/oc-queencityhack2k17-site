<?php namespace Look\Conversation\Models;

use Model;

/**
 * Class Status
 */
class Status extends Model
{	
	use \Look\Conversation\Traits\PolymorphicUserScopes;
	
	/**
     * Message is draft
     */
    const DRAFT = 'draft';

    /**
     * Message was not read
     */
    const UNREAD = 'unread';

    /**
     * Message was read
     */
    const READ = 'read';

    /**
     * Message was archived
     */
    const ARCHIVED = 'archived';

    /**
     * @var string The database table used by the model.
     */
    public $table = 'look_conversation_statuses';

    /**
     * @var array
     */
    protected $fillable = ['user_id', 'user_type', 'message_id', 'status'];

	//
	// Relations
	//
	
    public $belongsTo = [
        'message' => 'Look\Conversation\Models\Message',
    ];
    
    public $morphTo = [
	    'user' => [],
    ];
    
    
    public function __toString()
    {
	    return $this->attributes['status'];
    }
    
    //
    // Scopes
    //
    
    public function scopeParticipant($query, $participant)
    {
	    return $query->forUser($participant->user)->where('message_id', $participant->message_id);
    }
}