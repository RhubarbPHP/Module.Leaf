
var jqueryHtmlViewBridge = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.apply( this, arguments );
}

jqueryHtmlViewBridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge();
jqueryHtmlViewBridge.prototype.constructor = jqueryHtmlViewBridge;

jqueryHtmlViewBridge.prototype.onRegistered = function()
{
	this.element = $( this.viewNode );
}

window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge = jqueryHtmlViewBridge;