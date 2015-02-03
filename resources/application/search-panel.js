
var searchPanel = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );
}

searchPanel.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
searchPanel.prototype.constructor = searchPanel;

searchPanel.prototype.attachEvents = function()
{
    var self = this;

    this.element.find( "input:submit" ).click( function()
    {
        this.preventDefault = true;

        self.raiseServerEvent( "Search" );

        return false;
    } );
}

window.gcd.core.mvp.viewBridgeClasses.SearchPanel = searchPanel;