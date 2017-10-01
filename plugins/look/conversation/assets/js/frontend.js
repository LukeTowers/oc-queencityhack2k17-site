jQuery(document).ready(function($) {
	var $messageRow = $('.message-row');
	
	$messageRow.on('click', function(e) {
		$this = $(this);
		var href = $this.attr('data-href');
		var messageId = $this.attr('data-message-id');
		
		if (messageId) {
			$.request('onClickMessageRow', {
		    	data: {
			    	messageId: messageId
			    },
		    	success: function(data) {
			    	this.success(data).done(function() {
				    	$messageRow.removeClass('unread');
				    	$this.addClass('unread');
			    	});
		    	}
		    });
		}
		
		if (href) {
			window.location = href;
		}
	});
});