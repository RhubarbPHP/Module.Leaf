var bridge = function (presenterPath)
{
	window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.apply( this, arguments );
}

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.attachEvents = function ()
{
	var self = this;
};


bridge.prototype.setValue = function( value ) {
	var self = this;

	if ( typeof value == "string" || value instanceof String )
	{
		var dateTime = self.parseIsoDatetime( value );
		var hours = dateTime.getHours();
		var minutes = dateTime.getMinutes();

		self.findChildViewBridge( "Hours" ).setValue( hours.pad() );
		self.findChildViewBridge( "Minutes" ).setValue( minutes.pad() );
	}
};


bridge.prototype.parseIsoDatetime = function ( date )
{
	var newDate = date.split( /[: T-]/ ).map( parseFloat );
	return new Date( newDate[ 0 ], newDate[ 1 ] - 1, newDate[ 2 ], newDate[ 3 ] || 0, newDate[ 4 ] || 0, newDate[ 5 ] || 0, 0 );
};

Number.prototype.pad = function( size )
{
	var value = String( this );

	if( value >= 10 )
	{
		return value;
	}

	while( value.length < (size || 2) )
	{
		value = "0" + value;
	}

	return value;
}
bridge.prototype.hasValue = function( value )
{
	return true;
};

bridge.prototype.getValue = function()
{
	return true;
};

window.gcd.core.mvp.viewBridgeClasses.TimeViewBridge = bridge;