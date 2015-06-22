var jqueryHtmlViewBridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

jqueryHtmlViewBridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
jqueryHtmlViewBridge.prototype.constructor = jqueryHtmlViewBridge;

jqueryHtmlViewBridge.prototype.onRegistered = function () {
    this.element = $(this.viewNode);
};

window.rhubarb.viewBridgeClasses.JqueryHtmlViewBridge = jqueryHtmlViewBridge;