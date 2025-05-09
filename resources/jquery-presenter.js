var jqueryHtmlViewBridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

jqueryHtmlViewBridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
jqueryHtmlViewBridge.prototype.constructor = jqueryHtmlViewBridge;

jqueryHtmlViewBridge.prototype.onRegistered = function () {
    this.element = jQuery(this.viewNode);

    (function($) {
        if (typeof DOMPurify !== 'undefined') {
            const functions = ['html', 'append', 'prepend', 'before', 'after', 'replaceWith'];
            functions.forEach(function(name) {
                const func = $.fn[name];

                $.fn[name] = function(...args) {
                    if (typeof args[0] === 'string') {
                        args[0] = DOMPurify.sanitize(args[0]);
                    }
                    return func.apply(this, args);
                };
            });
        }
    })(jQuery);
};

jqueryHtmlViewBridge.prototype.hide = function () {
    this.element.hide();
};

jqueryHtmlViewBridge.prototype.show = function () {
    this.element.show();
};

window.rhubarb.viewBridgeClasses.JqueryHtmlViewBridge = jqueryHtmlViewBridge;
