<?php namespace TeamAte\Ccrezqs\Components;

use Auth;
use Flash;
use Cms\Classes\ComponentBase;

class ApplicationFoster extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'ApplicationFoster Component',
            'description' => 'No description provided yet...'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $user = Auth::getUser();
        if (!$user || ($user->foster_status !== 'registered')) {
            Flash::info('You have already submitted an application');
            return redirect('/foster');
        }
    }

    public function onApply()
    {
        $user = Auth::getUser();
        if (!$user) {
            throw new \ApplicationException("You must be logged in to apply!");
        }

        $input = input();

        try {
            if (!empty($input)) {
                $user->fill($input);
                $user->foster_status = 'applied';
                $user->save();
                Flash::success("Your application has been submitted.");
                return redirect('/foster');
            }
        } catch (\Exception $e) {
            throw new ApplicationException("Something went wrong: " . $e->getMessage());
        }
    }
}
