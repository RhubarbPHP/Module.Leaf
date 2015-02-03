var bridge = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.apply( this, arguments );
}

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.attachEvents = function()
{

}

bridge.spawn = function( spawnSettings, viewIndex )
{
	var element = document.createElement( "INPUT" );
	element.setAttribute( "type", "file" );

	window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.applyStandardAttributesToSpawnedElement( element, spawnSettings, viewIndex );

	return element;
}

window.gcd.core.mvp.viewBridgeClasses.SimpleHtmlFileUploadViewBridge = bridge;