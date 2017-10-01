/*
 * Scripts for the ThreadBehavior handling.
 */
+function ($) { "use strict";

    var ThreadBehavior = function() {

        this.clickMessage = function(messageId) {
            var newPopup = $('<a />'),
                $container = $('#view-message-'+messageId),
                requestData = paramToObj('data-request-data', $container.data('request-data'))

            newPopup.popup({
                handler: 'onThreadClickMessage',
                size: 'huge',
                extraData: $.extend({}, requestData, { messageId: messageId })
            })
        }

        /*
         * This function transfers the supplied variables as hidden form inputs,
         * to any popup that is spawned within the supplied container. The spawned 
         * popup must contain a form element.
         */
        this.bindToPopups = function(container, vars) {
            $(container).on('show.oc.popup', function(event, $trigger, $modal){
                var $form = $('form', $modal)
                $.each(vars, function(name, value){
                    $form.prepend($('<input />').attr({ type: 'hidden', name: name, value: value }))
                })
            })
        }

        function paramToObj(name, value) {
            if (value === undefined) value = ''
            if (typeof value == 'object') return value

            try {
                return JSON.parse(JSON.stringify(eval("({" + value + "})")))
            }
            catch (e) {
                throw new Error('Error parsing the '+name+' attribute value. '+e)
            }
        }

    }

    $.oc.threadBehavior = new ThreadBehavior;
}(window.jQuery);