<?php namespace Look\Essentials\Components;

use Cms\Classes\ComponentBase;
use RainLab\User\Components\Account;
use Auth;
use Flash;
use Lang;
use Redirect;

/**
 * Class UserSettings
 * @package Look\Essentials\Components
 *
 * Handles user settings.
 *
 * Code for the onUpdate function was taken from Rainlab.Users
 * https://github.com/rainlab/user-plugin
 * More details found inside of the function
 */
class UserSettings extends ComponentBase
{

    public function componentDetails()
    {
        return [
            'name'        => 'UserSettings',
            'description' => 'A form allowing clients to change their settings.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    /**
     * Sets a page variable so we can fill the form with pre-existing values
     */
    public function onRun()
    {
        $account = new Account();
        $this->page['user'] = $account->user();
    }

    /**
     * AJAX handler to update the model when the form is saved.
     * Refreshes the current page.
     */
    public function onUpdate()
    {

        $account = new Account();

        /**
         * The following code was taken from RainLab.Users plugin. Specifically from the Account
         * component. It was copied instead of used directly to avoid a redirection which occurs at the end of
         * the onUpdate function.
         */
        if (!$user = $account->user()) {
            return;
        }

        $data = post();

        $user->fill($data);
        $user->save();

        /*
         * Password has changed, reauthenticate the user
         */
        if (strlen(post('password'))) {
            Auth::login($user->reload(), true);
        }

        Flash::success(post('flash', Lang::get('rainlab.user::lang.account.success_saved')));

        return Redirect::refresh();
    }

}
