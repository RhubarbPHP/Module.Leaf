
var button = function( presenterPath )
{
    this.validation = false;
	this.validator = false;
	this.confirmMessage = false;

    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );

    this.useXmlRpc = ( this.element.attr( "xmlrpc" ) == "yes" );

	if ( this.element.attr( "confirm" ) )
	{
		this.confirmMessage = this.element.attr( "confirm" );
	}

    if ( this.element.attr( "validation" ) )
    {
        var validationString = this.element.attr( "validation" );

        this.validation = window.gcd.core.validation.Validator.fromJson( eval( '(' + validationString + ')' ) );
    }

	if ( this.element.attr( "validator" ) )
	{
		this.validator = this.element.attr( "validator" );
	}
};

button.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
button.prototype.constructor = button;

button.prototype.attachEvents = function()
{
	var self = this;

	this.element.click( function()
	{
        if ( self.validation )
        {
			var validationHost = self.eventHost;

			if ( self.validator && gcd.core.mvp.registeredPresenters[ self.validator ] )
			{
				validationHost = gcd.core.mvp.registeredPresenters[ self.validator ];
			}
			else
			{
				// See if there is a model-provider viewBridge in our parent chain and use the first of these
				// as our source of data collection.

				if ( self.element.parents( '.model-provider').length > 0 )
				{
					validationHost = self.element.parents( '.model-provider:first' )[0].viewBridge;
				}
			}

            if ( validationHost.validate( self.validation ) !== true )
            {
				self.raiseClientEvent( "ValidationFailed" );
				window.gcd.core.validation.Scrolled = false;
                return false;
            }
        }


		if ( self.confirmMessage )
		{
			if ( !confirm( self.confirmMessage ) )
			{
				this.preventDefault = true;
				return false;
			}
		}

		if ( self.raiseClientEvent( "OnButtonPressed" ) === false )
		{
			this.preventDefault = true;
			return false;
		}

        if ( self.useXmlRpc )
        {
		    self.raiseServerEvent( "ButtonPressed", function( response )
			{
				self.raiseClientEvent( "ButtonPressCompleted", response );
			});

		    this.preventDefault = true;
            return false;
        }

		if ( self.element.hasClass( 'submit-on-click' ) && self.element.attr( 'type' ) == 'button' )
		{
			self.element.attr( 'type', 'submit' );
			window.setTimeout( function()
			{
				self.element.attr( 'type', 'button' );
			}, 1 );
		}
	} );
};

window.gcd.core.mvp.viewBridgeClasses.Button = button;