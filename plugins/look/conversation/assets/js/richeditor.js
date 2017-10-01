+function ($) { "use strict";
    $(document).render(function() {
        if ($.FroalaEditor) {
            $.FroalaEditor.DEFAULTS = $.extend($.FroalaEditor.DEFAULTS, {
                emoticonsUseImage: false
            });
        }        
    })
}(window.jQuery);