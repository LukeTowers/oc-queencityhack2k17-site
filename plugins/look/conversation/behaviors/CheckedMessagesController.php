<?php namespace Look\Conversation\Behaviors;

use Flash;
use October\Rain\Support\Collection;
use Backend\Classes\ControllerBehavior;

use Look\Conversation\Models\Message as MessageModel;

/**
 * Class CheckedMessagesController
 */
class CheckedMessagesController extends ControllerBehavior
{

    public function index_onMarkAsRead()
    {
        $this->checkedMessages(true)->each(function ($message) {
            $message->markRead();
        });
        
        Flash::success(trans('look.conversation::lang.controllers.general.messages.read_selected_success'));

        return $this->listRefresh();
    }
    
    public function index_onMarkAsUnread()
    {
	    $this->checkedMessages(false)->each(function ($message) {
            $message->markUnread();
        });
        
        Flash::success(trans('look.conversation::lang.controllers.general.messages.read_selected_success'));

        return $this->listRefresh();
    }

    public function index_onArchive()
    {
        $this->checkedMessages(true)->each(function ($message) {
            $message->archive();
        });

        Flash::success(trans('look.conversation::lang.controllers.general.messages.archive_selected_success'));

        return $this->listRefresh();
    }
    
    public function index_onRestore()
    {
        $this->checkedMessages(true)->each(function ($message) {
            $message->restoreArchived();
        });

        Flash::success(trans('look.conversation::lang.controllers.general.messages.restore_selected_success'));

        return $this->listRefresh();
    }
    

	/**
	 * Get a collection of messages to be manipulated based on the posted checked ids
	 *
	 * @param bool $includeSiblings Include messages in the same thread for each of the messages being returned
	 * @return Collection $messages
	 */
    protected function checkedMessages($includeSiblings = false)
    {
	    $checkedIds = post('checked');
	    
	    $messages = new Collection();
	    if (is_array($checkedIds) && count($checkedIds)) {
		    $messages = MessageModel::whereIn('id', $checkedIds)->with('thread')->get();
		    if ($includeSiblings) {
			    $searchMessages = clone $messages;
			    foreach ($searchMessages as $message) {
				    $messageSiblings = $message->thread->messages()->notCurrent($message)->get();
				    foreach ($messageSiblings as $messageSibling) {
					   $messages->push($messageSibling);
				    }
			    }
		    }
		    
		    $messages->unique(function ($item) {
			    return $item->id;
		    });
	    } else {
		    throw new \Exception('test');
	    }
	    
	    return $messages;
    }

	/**
	 * Refresh the associated controller's list behavior
	 */
    protected function listRefresh()
    {
        return $this->controller->listRefresh();
    }

}