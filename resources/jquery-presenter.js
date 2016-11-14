var jqueryHtmlViewBridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

jqueryHtmlViewBridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
jqueryHtmlViewBridge.prototype.constructor = jqueryHtmlViewBridge;

jqueryHtmlViewBridge.prototype.onRegistered = function () {
    this.element = jQuery(this.viewNode);
};

jqueryHtmlViewBridge.prototype.hide = function () {
    this.element.hide();
};

jqueryHtmlViewBridge.prototype.show = function () {
    this.element.show();
};

window.rhubarb.viewBridgeClasses.JqueryHtmlViewBridge = jqueryHtmlViewBridge;
