
var formPager = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );
}

formPager.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
formPager.prototype.constructor = formPager;

formPager.prototype.attachEvents = function()
{
    var self = this;

    this.element.find( ".pages a" ).click( function()
    {
        self.element.find( ".page-input").val( $( this ).data( 'page' ) );
        self.element.parents( 'form' )[0].submit();

        return false;
    } );
}

window.gcd.core.mvp.viewBridgeClasses.FormPager = formPager;