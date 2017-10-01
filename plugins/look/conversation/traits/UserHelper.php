<?php namespace Look\Conversation\Traits;

use App;
use Auth;

use BackendAuth as StaffAuth;
use RainLab\User\Models\User as ClientModel;
use Backend\Models\User as StaffModel;

trait UserHelper
{
	// Gets the current user
	public static function getCurrentUser()
	{
		// Determine what type of user to look for
	    if (App::runningInBackend()) {
		    $user = StaffAuth::getUser();
	    } else {
		    $user = Auth::getUser();
	    }

	    return $user;
	}

	// Returns the current user if the provided user is invalid
	public static function defaultToCurrentUser($user = null)
	{
		return static::userIsValid($user) ? $user : static::getCurrentUser();
	}

	// Validates that the provided user is valid
	public static function userIsValid($user)
	{
		if ((static::isStaff($user) || static::isClient($user)) && $user->exists) {
			return true;
		} else {
			return false;
		}
	}

	public static function compareUsers($user1, $user2)
	{
		return ((intval($user1->id) === intval($user2->id)) && (get_class($user1) === get_class($user2)));
	}

	// Detect user type
	public static function isClient($user)
	{
		return ($user instanceof ClientModel);
	}

	public static function isStaff($user)
	{
		return ($user instanceof StaffModel);
	}
}