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



(function(){

    $('.bs-component [data-toggle="popover"]').popover();
    $('.bs-component [data-toggle="tooltip"]').tooltip();

    $(".icons-material .icon").each(function() {
        $(this).after("<br><br><code>" + $(this).attr("class").replace("icon ", "") + "</code>");
    });

})();