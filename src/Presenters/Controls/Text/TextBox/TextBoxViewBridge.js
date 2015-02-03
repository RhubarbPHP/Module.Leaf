
var bridge = function( presenterPath )
{
	window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.apply( this, arguments );
}

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.spawn = function( spawnData, index )
{
	var textBox = document.createElement( "INPUT" );
	textBox.setAttribute( "type", "text" );

	window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.applyStandardAttributesToSpawnedElement( textBox, spawnData, index );

	return textBox;
}

bridge.prototype.attachEvents = function()
{

}

window.gcd.core.mvp.viewBridgeClasses.TextBoxViewBridge = bridge;

