var bridge = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.apply( this, arguments );
}

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onSubPresenterValueChanged = function()
{
	if ( this.model.AutoSubmit )
	{
		this.raiseServerEvent( "Search" );
	}
}

window.gcd.core.mvp.viewBridgeClasses.SearchPanelViewBridge = bridge;