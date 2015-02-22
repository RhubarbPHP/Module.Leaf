var bridge = function( presenterPath )
{
	this.entries = [];
	this.controlSpawnSettings = [];

    window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge.apply( this, arguments );
}

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.JqueryHtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.attachEvents = function()
{
	this.updateDom();
}

bridge.prototype.onStateLoaded = function()
{
	this.controlSpawnSettings = this.model.PresenterSpawnSettings;
}

bridge.prototype.wouldRequireNewEntry = function( entry )
{
	return false;
}

bridge.prototype.updateDom = function()
{
	var needsNewEntry = true;

	if ( this.entries.length > 0 )
	{
		needsNewEntry = this.wouldRequireNewEntry( this.entries[ this.entries.length - 1 ] );
	}

	if ( needsNewEntry )
	{
		// Spawn the entries and then delegate the layout of them to a specialised function.
		var index = this.entries.length;
		var entry = {};

		for( var i in this.controlSpawnSettings )
		{
			var spawnSettings = this.controlSpawnSettings[ i ];

			var control = window.gcd.core.mvp.spawn( spawnSettings, index );
			entry[ control.viewBridge.presenterName ] = control;
		}

		this.entries[ this.entries.length ] = entry;

		var entryDom = this.layoutControls( entry );

		this.element.append( entryDom );

		for ( var i in entry )
		{
			if ( entry[i].viewBridge )
			{
				entry[i].viewBridge.onParentsReady();
			}
		}

		entry.dom = entryDom;
	}
}

/**
 * Returns a dom element of a container that contains the laid out controls.
 *
 * @param controlsCollection
 */
bridge.prototype.layoutControls = function( controlsCollection )
{

}

bridge.prototype.onSubPresenterValueChanged = function()
{
	this.updateDom();
}

window.gcd.core.mvp.viewBridgeClasses.RepeatingCompositeControlViewBridge = bridge;