(function ($) {
    var T = window['tatty'] = {};

    T.printf = function () {
        var args = arguments;

        return args[0].replace(/\{([0-9]+)\}/g, function () {
            return args[parseInt(arguments[1]) + 1];
        });
    }

    T.showMsg = function (type, messageContent) {
        var containerId = 'tatty-msg-container';
        var msgContainer = $('#' + containerId).get(0);
        var msgTpl = '<div class="ui {0} message">{1}</div>';

        if (!msgContainer) {
            msgContainer = document.createElement('div');
            msgContainer.id = containerId;

            $(msgContainer).css({
                width: '100%',
                'test-align': 'center',
                position: 'absolute',
                top: '5px'
            });

            $(msgContainer).html('<div id="tatty-msg-inner" style="max-width: 400px;margin: 0 auto;"></div>');
            document.body.appendChild(msgContainer);
        }

        $('#tatty-msg-inner').html(T.printf(msgTpl, type, messageContent));
        $(msgContainer).fadeIn(300);

        setTimeout(function () {
            tatty.hideMsg();
            console.log('test');
        }, 3000);
    };

    T.hideMsg = function () {
        var containerId = 'tatty-msg-container';
        $('#' + containerId).fadeOut(300);
    };
})(jQuery);