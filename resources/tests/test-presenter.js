
var testPresenter = function( presenterPath )
{
	window.HtmlViewBridge.apply( this, arguments );
}

testPresenter.prototype = Object.create( window.HtmlViewBridge.prototype );

testPresenter.prototype.eatMonkeysResponseReceived = function( response )
{
	return response + 'sick';
}

window.TestPresenter = testPresenter;