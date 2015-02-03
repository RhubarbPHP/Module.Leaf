var bridge = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.MultipleHtmlFileUploadViewBridge.apply( this, arguments );
};

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.MultipleHtmlFileUploadViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onStateLoaded = function()
{
	if ( !this.model.maxFileSize )
	{
		this.model.maxFileSize = 5 * 1024 * 1024;
	}
};

bridge.prototype.filesSelected = function( files )
{
    this.dragStateChanged( false );

    window.gcd.core.mvp.viewBridgeClasses.MultipleHtmlFileUploadViewBridge.prototype.filesSelected.apply( this, arguments );
};

/**
 * This function should set this.dropZone to a reference to the HTML element for the drop zone.
 *
 * This might involved creating that DOM element in the first place or simply using an existing page element.
 */
bridge.prototype.getOrCreateDropZoneElement = function()
{
    var eventElement = this.raiseClientEvent( "GetDropZoneElement" );

    if ( eventElement )
    {
        this.dropZone = eventElement;
        return;
    }

    this.dropZone = document.createElement( "div");
    this.dropZone.style.display = 'none';
    this.dropZone.className = "drag-and-drop-upload";

    var inner = document.createElement( "div" );
    inner.className = "_drop-zone";

    this.dropZone.appendChild( inner );
    this.viewNode.appendChild( this.dropZone );

    return this.dropZone;
};

bridge.prototype.onParentsReady = function()
{
	window.gcd.core.mvp.viewBridgeClasses.MultipleHtmlFileUploadViewBridge.prototype.onParentsReady.apply( this, arguments );

    var self = this;

	if ( this.supportsHtml5Uploads() )
	{
        // Generate and attach the drop zone element to the DOM
        this.getOrCreateDropZoneElement();

        // Remove the file upload box.
		this.viewNode.removeChild( this.uploadInput );
		this.dropZone.style.display = 'block';

		this.dropZone.addEventListener( "dragover", function(e)
		{
			e.stopPropagation();
			e.preventDefault();
			self.dragStateChanged( true );
		}, false );

		this.dropZone.addEventListener( "dragleave", function(e)
		{
			e.stopPropagation();
			e.preventDefault();
			self.dragStateChanged( false );
		}, false );

		this.dropZone.addEventListener( "drop", function( e )
		{
			e.stopPropagation();
			e.preventDefault();

			var files = e.target.files || e.dataTransfer.files;

			self.filesSelected( files );

			return false;
		}, false );
	}
};

bridge.prototype.dragStateChanged = function( dragging )
{
	if ( dragging )
	{
		this.addClass( this.dropZone, "-is-dragging" );
	}
	else
	{
		this.removeClass( this.dropZone, "-is-dragging" );
	}
};

window.gcd.core.mvp.viewBridgeClasses.DragAndDropFileUploadViewBridge = bridge;