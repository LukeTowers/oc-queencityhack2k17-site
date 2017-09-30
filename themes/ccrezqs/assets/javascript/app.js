/*
 * Application
 */
$(document).ready(function() {
	// This command is used to initialize some elements and make them work properly
	$.material.init();
	$(document).tooltip({
		selector: "[data-toggle=tooltip]"
	});
});

(function($) {
    "use strict";

    jQuery(document).ready(function($) {
	    /*-------------------------------
        OCTOBERCMS FLASH MESSAGE HANDLING
        ---------------------------------*/
	    $(document).on('ajaxSetup', function(event, context) {
		    // Enable AJAX handling of Flash messages on all AJAX requests
		    context.options.flash = true;

		    // Enable the StripeLoadIndicator on all AJAX requests
		    context.options.loading = $.oc.stripeLoadIndicator;

		    // Handle Flash Messages
			context.options.handleFlashMessage = function(message, type) {
				$.oc.flashMsg({ text: message, class: type });
			};

			// Handle Error Messages
			context.options.handleErrorMessage = function(message) {
				$.oc.flashMsg({ text: message, class: 'error' });
			};
		});
    });
})();

(function(){

    $('.bs-component [data-toggle="popover"]').popover();
    $('.bs-component [data-toggle="tooltip"]').tooltip();

    $(".icons-material .icon").each(function() {
        $(this).after("<br><br><code>" + $(this).attr("class").replace("icon ", "") + "</code>");
    });

})();