<?php namespace Look\Conversation\Traits;

use RainLab\User\Models\User as ClientModel;
use Look\StaffManager\Models\StaffMember as StaffModel;

trait PolymorphicUserScopes
{
	use UserHelper;
	
	/*
	 * Scopes
	 */
	
	public function scopeForUser($query, $user, $defaultToCurrent = false)
	{
		// Initialize variables
	    $user_id = 0;
	    $user_type = '';
	    
	    if ($defaultToCurrent) {
		    $user = static::defaultToCurrentUser($user);
	    }
		
		if (static::userIsValid($user)) {
		    $user_id = $user->id;
		    $user_type = get_class($user);
	    }
	    
        return $query->where('user_id', '=', $user_id)->where('user_type', '=', $user_type);
	}
	
	public function scopeNotForUser($query, $user, $defaultToCurrent = false)
	{
		// Initialize variables
	    $user_id = 0;
	    $user_type = '';
		
		if ($defaultToCurrent) {
		    $user = static::defaultToCurrentUser($user);
	    }
		
		if (static::userIsValid($user)) {
		    $user_id = $user->id;
		    $user_type = get_class($user);
	    }
	    
        return $query->where('user_id', '!=', $user_id)->where('user_type', '!=', $user_type);
	}
	
    public function scopeForCurrentUser($query)
    {   
        return $query->forUser($this->getCurrentUser());
    }
    
    public function scopeUserIsStaff($query)
	{
		return $query->where('user_type', '=', StaffModel::class);
	}
	
	public function scopeUserIsClient($query)
	{
		return $query->where('user_type', '=', ClientModel::class);
	}
}