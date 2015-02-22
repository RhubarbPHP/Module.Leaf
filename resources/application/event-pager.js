
var eventPager = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );
}

eventPager.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
eventPager.prototype.constructor = eventPager;

eventPager.prototype.attachEvents = function()
{
    var self = this;

    this.element.find( ".pages a" ).click( function()
    {
        self.element.find( ".page-input").val( $( this ).data( 'page' ) );

        // If our presenters are configured for it we also notify the
        // server side with an event.

        self.raiseServerEvent( "PageChanged", $( this ).data( 'page' ) );

        return false;
    } );
}

window.gcd.core.mvp.viewBridgeClasses.EventPager = eventPager;