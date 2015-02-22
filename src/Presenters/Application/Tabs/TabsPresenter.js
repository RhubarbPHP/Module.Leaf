
var tabsPresenter = function( presenterPath )
{
	window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );
}

tabsPresenter.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
tabsPresenter.prototype.constructor = tabsPresenter;

tabsPresenter.prototype.attachEvents = function()
{
	var self = this;

	this.element.find( 'li').click( function()
	{
		var lis = $( this ).parent()[0].childNodes;
		var index = Array.prototype.indexOf.call( lis, this );

		self.raiseServerEvent( "TabSelected", index );

        $( 'ul:first', self.element ).children().removeClass( '-is-selected' );
        $( this).addClass( '-is-selected' );
	});
}

window.gcd.core.mvp.viewBridgeClasses.Tabs = tabsPresenter;