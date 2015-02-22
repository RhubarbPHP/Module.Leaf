
var dialog = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );

	this.originalSubPresenterStates = null;

	/* Set this to a callback to execute code when the dialog is shown
	 */
	this.onShow = function(){};
};

dialog.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
dialog.prototype.constructor = dialog;

dialog.prototype.attachEvents = function()
{

};

dialog.prototype.close = function()
{
	this.element.hide();
};

dialog.prototype.getOriginalStates = function()
{
	var subPresenters = this.getSubPresenters();

	for ( var i in subPresenters )
	{
		var subPresenter = subPresenters[ i ];

		if ( subPresenter.hasValue() )
		{
			if( this.originalSubPresenterStates == null )
			{
				this.originalSubPresenterStates = [];
			}

			this.originalSubPresenterStates[ subPresenter.presenterName ] = subPresenter.getValue();
		}
	}
}

dialog.prototype.clearAndShow = function()
{
	if ( this.originalSubPresenterStates == null )
	{
		this.getOriginalStates();
	}

	var subPresenters = this.getSubPresenters();

	for( var i in subPresenters )
	{
		var subPresenter = subPresenters[i];

		if ( !subPresenter.hasValue() )
		{
			continue;
		}

		if ( this.originalSubPresenterStates[ subPresenter.presenterName ] )
		{
			subPresenter.setValue( this.originalSubPresenterStates[ subPresenter.presenterName ] );
		}
		else
		{
				subPresenter.setValue( "" );
		}
	}

	this.show();
};

dialog.prototype.show = function()
{
    this.element.show();
	this.size();
	this.focusFirstInput();

	if ( this.onShow )
	{
		this.onShow();
	}
};

/**
 * Sets the focus on the first HTML input in the container.
 */
dialog.prototype.focusFirstInput = function()
{
	$( "input,select,textarea", this.element).first().focus();
}

/**
 * Displays the dialog after fetching data for the server
 *
 * @param uniqueIdentifier The unique identifier for the record to show the data for.
 * @param callback An optional callback function called just before the dialog is show and after the data is fetched.
 * 				   Often used to perform some DOM manipulation just after loading a dialog for a particular record based
 * 				   upon the loaded data. The dialog data from the server is passed through.
 */
dialog.prototype.getDataAndShow = function( uniqueIdentifier, callback )
{
	var self = this;

	this.getData( uniqueIdentifier, function( dialogData )
	{
		if ( callback )
		{
			callback( dialogData );
		}

		self.show();
	})
};

dialog.prototype.onDialogDataFetched = function( dialogData )
{

};

dialog.prototype.getData = function( uniqueIdentifier, callback )
{
	var self = this;

	this.raiseServerEvent( "GetDialogData", uniqueIdentifier, function( dialogData )
	{
		self.onDialogDataFetched( dialogData );

		self.findInputsAndPopulate( dialogData );
		self.dialogData = dialogData;

		callback( dialogData );
	});
};

dialog.prototype.size = function()
{
	var dialog = this.element.find( ".dialog");

	if ( this.model.PreferredWidth )
	{
		dialog.outerWidth( this.model.PreferredWidth );
	}

	if ( this.model.PreferredHeight )
	{
		dialog.outerHeight( this.model.PreferredHeight );
	}

	dialog.css( "marginLeft", "-" + (dialog.outerWidth()/2) + "px" );
	dialog.css( "marginTop", "-" + (dialog.outerHeight()/2) + "px" );
};



window.gcd.core.mvp.viewBridgeClasses.DialogViewBridge = dialog;