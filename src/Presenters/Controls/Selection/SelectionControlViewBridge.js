
var selectionControl = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );

    /**
     * Set to false if the selection control can't support multiple selections
     * at once.
     *
     * @type {boolean}
     */
    this.supportsMultipleSelection = true;
}

selectionControl.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
selectionControl.prototype.constructor = selectionControl;

selectionControl.prototype.attachEvents = function()
{

}

selectionControl.prototype.setCurrentlyAvailableSelectionItems = function( items )
{

}

selectionControl.prototype.getValue = function()
{
    // If the control only supports a single selection then just return
    // the first of the selected items (or false if none selected)
    if ( !this.supportsMultipleSelection )
    {
        if ( this.model.SelectedItems.length > 0 )
        {
            return this.model.SelectedItems[0].value;
        }
        else
        {
            return false;
        }
    }
    else
    {
		var values = [];

		$( this.viewNode ).find( "input:checked" ).each( function() {
			values.push( $( this ).val() );
		} );

		return values;
    }
};

selectionControl.prototype.setSelectedItems = function( items )
{
    this.model.SelectedItems = items;
};

selectionControl.prototype.getSelectedItems = function()
{
	return this.model.SelectedItems;
};

/**
 * Returns the first of the selected item objects
 *
 * @returns {*|SelectedItems}
 */
selectionControl.prototype.getSelectedItem = function()
{
	if ( this.model.SelectedItems.length <= 0 )
	{
		return false;
	}

	return this.model.SelectedItems[0];
};

selectionControl.prototype.isValueSelected = function( value )
{
	return this.getSelectedKeyFromValue( value ) != -1;
}

selectionControl.prototype.getSelectedKeyFromValue = function( value )
{
	for( var i in this.model.SelectedItems )
	{
		if( i != "length" && this.model.SelectedItems[i].value == value )
		{
			return i;
		}
	}
	return -1;
}

selectionControl.prototype.hasValue = function()
{
	return true;
};

selectionControl.prototype.fetchAvailableSelectionItems = function()
{
    var params = [ "UpdateAvailableSelectionItems" ];

    for( var i = 0; i < arguments.length; i++ )
    {
        params[ params.length ] = arguments[i];
    }

    var self = this;

    params[ params.length ] = function( items )
    {
        self.setCurrentlyAvailableSelectionItems( items );
    };

    this.raiseServerEvent.apply( this, params );
};

window.gcd.core.mvp.viewBridgeClasses.SelectionControlViewBridge = selectionControl;
