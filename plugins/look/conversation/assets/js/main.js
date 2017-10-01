$(function () {

    $.ajax({
        url: "/backend/look/conversation/inbox/unreadCount",
        success: function (result) {
            if (result > 0) {
                $('.nav .icon-comments').parents('a').append('<span class="counter">' + result + '</span>');
            }
        }
    });

});