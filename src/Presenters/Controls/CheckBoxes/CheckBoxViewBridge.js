var bridge = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.apply( this, arguments );
}

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.attachEvents = function()
{

};

bridge.prototype.setValue = function( value )
{
	if( value || value == 1 )
	{
		this.viewNode.checked = true;
	}
	else
	{
		this.viewNode.checked = false;
	}
};

bridge.prototype.getValue = function()
{
	if ( this.viewNode.checked )
	{
		return this.viewNode.value;
	}
	else
	{
		return false;
	}
};

bridge.spawn = function( spawnSettings, viewIndex )
{
	var checkbox = document.createElement( 'input' );
	checkbox.setAttribute( 'type', 'checkbox' );
	checkbox.setAttribute( 'value', '1' );
	checkbox.setAttribute( 'checked', spawnSettings.Checked );

	for( var i in spawnSettings.Attributes )
	{
		checkbox.setAttribute( i, spawnSettings.Attributes[i] );
	}

	window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge.applyStandardAttributesToSpawnedElement( checkbox, spawnSettings, viewIndex );

	return checkbox;
};

window.gcd.core.mvp.viewBridgeClasses.CheckBoxViewBridge = bridge;