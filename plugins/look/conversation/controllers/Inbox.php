<?php namespace Look\Conversation\Controllers;

use BackendMenu;
use Form as FormHelper;
use Backend\Classes\Controller;
use October\Rain\Support\Collection;

use RainLab\User\Models\User as UserModel;

use Look\Conversation\Classes\MessageService;
use Look\Conversation\Models\Message as MessageModel;
use Look\Conversation\Models\Participant as ParticipantModel;

/**
 * Class Inbox
 */
class Inbox extends Controller
{
	public $requiredPermissions = ['look.conversation.access_inbox'];
	
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController',
        'Backend.Behaviors.RelationController',
        'Look.Conversation.Behaviors.MessagesController',
        'Look.Conversation.Behaviors.CheckedMessagesController',
    ];

    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';
    public $relationConfig = '$/look/conversation/controllers/configs/relations.recipients.yaml';

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Look.Conversation', 'conversation', 'inbox');
    }
    
    public function create()
    {
	    
	    // TODO: Get the participant information to display on page load
	    parent::create();
	    
/*
	WIP passing client id from cases screen
	    
	    $clientId = (int) input('client_id');
	    
// 	    dd($clientId);
	    
	    if ($clientId) {
		    $client = UserModel::currentStaffCanInteractWith()->where('id', $clientId)->first();
		    if ($client) {
			    $getSessionKey = function () {
			        if (post('_session_key')) {
			            return post('_session_key');
			        } else {
				        return FormHelper::getSessionKey();
				    }
			    };
			    
			    $sessionKey = $getSessionKey();
			    
			    // NOT WORKING BECAUSE FORMCONTROLLER BEHAVIOR STUBS IT
			    $this->addDynamicMethod('formExtendModel', function ($message) use ($client, $sessionKey) {
				    dd($message);
				    
				    
				    if ($message->participants()->withDeferred($sessionKey)->get()->isEmpty()) {
					    $participants = new Collection();
					    $participants->push(ParticipantModel::create([
						    'user_id'   => $client->id,
						    'user_type' => get_class($client),
					    ]));
					    $message->client_recipient = $participants->first();
					    $message->participants()->addMany($participants, $sessionKey);
					    
					    return $message;
					}
			    });
			    
			    $this->formExtendModel(null);
		    }
	    }
	    
	    // TODO: Get the participant information to display on page load
	    parent::create();
	    
	    dd('test');
	    
*/
    }

    public function listExtendQuery($query)
    {
	    return $query->latestReceivedGroupedByThread();
    }
    
    public function listInjectRowClass($message)
    {
        if ($message->isUnread()) {
            return 'new';
        }
    }

    public function unreadCount()
    {
        return response(MessageService::getScopeCount('unread'));
    }
}