<?php return [
    'plugin' => [
        'name' => 'Conversation',
        'description' => 'Conversation plugin.',
    ],
    
    'navigation' => [
        'messages'  => 'Messages',
        'inbox'     => 'Inbox',
        'outbox'    => 'Sent',
        'drafts'    => 'Drafts',
        'archive'   => 'Archive',
        'templates' => 'Templates'
    ],
    
    'permissions' => [
        'tab' => 'Conversations',
        
        'access_inbox'     => 'Access Inbox',
        'access_outbox'    => 'Access Outbox',
        'access_drafts'    => 'Access Drafts',
        'access_archive'   => 'Access Archive',
        'manage_templates' => 'Manage Templates',
        'manage_settings'  => 'Manage Settings',
    ],
    
    'settings' => [
	    'label'       => 'Conversation settings',
	    'description' => 'Manage Conversation / Messaging settings',
	    'default_client_recipient' => [
		    'label'   => 'Default Client Recipient',
		    'comment' => 'The staff member that client messages will be sent to if they do not have a current active therapist assigned to them.',
	    ],
	    'system_messages_from' => [
		    'label'   => 'System Messages Author',
		    'comment' => 'The staff member that system messages will be displayed as being sent from.',
	    ],
    ],
    
    'controllers' => [
	    'general' => [
		    'contexts' => [
			    'create'  => 'New message',
			    'update'  => 'Update draft',
			    'preview' => 'View message',
			    'reply'   => 'Reply to message',
			    'forward' => 'Forward message',
		    ],
		    'filters' => [
			    'unread' => 'Unread',
			    'read'   => 'Read',
			    'has_attachments' => 'Has attachments',
			    'no_attachments' => 'Doesn\'t have attachments',
		    ],
		    'buttons' => [
			    'new'     => 'New',
			    'new_message' => 'New message',
			    'save'    => 'Save',
			    'send'    => 'Send',
			    'unsend'  => 'Revert send',
			    'reply'   => 'Reply',
			    'forward' => 'Forward',
			    'return'  => 'Return',
			    'archive' => 'Archive',
			    'restore' => 'Restore',
			    'delete'  => 'Delete',
			    
			    'archive_selected' => 'Archive selected',
			    'restore_selected' => 'Restore selected',
			    'delete_selected'  => 'Delete selected',
				'read_selected'    => 'Mark selected as read',
				'unread_selected'  => 'Mark selected as unread',
				
				'select_client'   => 'Select Client',
			    'remove_client'   => 'Remove Client',
			    'select_staff'    => 'Add Staff Members',
			    'remove_selected' => 'Remove selected recipients',
			    'add_staff_recipients' => 'Send to Staff Members',
		    ],
		    'messages' => [
			    'no_messages'         => 'You currently do not have any messages.',
			    'no_siblings'         => 'No other messages in this thread.',
			    'no_drafts'           => 'Hmmm, there\'s no drafts here. Save a message without sending it and you can work on it here.',
			    'no_sent'             => 'You haven\'t sent any messages yet.',
			    'no_archived'         => 'You don\'t have any archived messages yet. Is your inbox getting cluttered?',
			    
				'no_staff_selected'   => 'No staff members have been specified',
				'no_assigned_users'   => 'You don\'t have access to any users. Are you assigned to any cases?',
				'no_client_selected'  => 'Not specified',
				'no_messages_generic' => 'There are no messages to display here.',
				
				
				'add_client' => 'Add client to message',
				'add_staff'  => 'Add staff members to message',
				'pick_recipient' => 'Click the %s button to select the recipient',
				'template_instructions' => 'Templates will only replace the message subject and/or body when the existing value for each is empty',
			    
				'sending' => 'Sending message...',
				'sent'    => 'Message was sent.',
				'unsent'  => 'Message was reverted.',
				'saving'  => 'Saving message...',
				'saved'   => 'Message was saved.',
				'archiving' => 'Archiving message...',
				'archived'  => 'Message was archived.',
				'restoring' => 'Restoring message...',
				'restored'  => 'Message was restored.',
				'deleting'  => 'Deleting message...',
				'deleted'   => 'Message was deleted',
				
				'archive_selected_confirm' => 'Are you sure you want to archive the selected messages?',
				'archive_selected_success' => 'Successfully archived the selected messages.',
				'restore_selected_confirm' => 'Are you sure you want to restore the selected messages?',
				'restore_selected_success' => 'Successfully restored the selected messages.',
				'delete_selected_confirm' => 'Are you sure you want to permanently delete the selected messages?',
				'delete_selected_success' => 'Successfully deleted the selected messages.',
				'read_selected_confirm'    => 'Are you sure you want to mark the selected messages as read?',
				'read_selected_success'    => 'Successfully marked the selected messages as read.',
				'unread_selected_confirm'  => 'Are you sure you want to mark the selected messages as unread?',
				'unread_selected_success'  => 'Successfully marked the selected messages as unread.',
		    ],
	    ],
    ],
    
    'models' => [
	    'general' => [
		    'created_at' => 'Created at',
		    'updated_at' => 'Updated at',
	    ],
	    'message' => [
		    'label' => 'Message',
		    'label_plural' => 'Messages',
		    'from' => 'From',
		    'to' => 'To',
		    'subject' => 'Subject',
		    'body' => 'Message',
		    'excerpt' => 'Preview',
		    'attachments' => 'Attachments',
		    'sent_at' => [
		    	'sent' => 'Sent',
		    	'received' => 'Received',
		    	'general' => 'Date sent',
		    ],
	    ],
	    'participant' => [
		    'label'        => 'Recipient',
		    'label_plural' => 'Recipients',
		    'type'         => [
			    'client' => 'Client',
			    'staff'  => 'Staff Member',
		    ],
	    ],
	    'status' => [
		    'label'        => 'Status',
		    'label_plural' => 'Statuses',
	    ],
	    'thread' => [
		    'label' => 'Conversation',
	    ],
	    'template' => [
		    'label'        => 'Template',
		    'label_plural' => 'Templates',
		    'name'         => 'Name',
	    ],
    ],
];
