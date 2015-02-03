var bridge = function( presenterPath )
{
    window.gcd.core.mvp.viewBridgeClasses.SimpleHtmlFileUploadViewBridge.apply( this, arguments );
};

bridge.prototype = new window.gcd.core.mvp.viewBridgeClasses.SimpleHtmlFileUploadViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onStateLoaded = function()
{
    if ( !this.model.MaxFileSize )
    {
        this.model.MaxFileSize = 5 * 1024 * 1024;
    }
};

bridge.prototype.supportsHtml5Uploads = function()
{
    var xhr = new XMLHttpRequest();

    if ( !xhr.upload || !window.File || !window.FileList || !window.FileReader )
    {
        return false;
    }

    return true;
};

bridge.prototype.attachEvents = function()
{
    this.uploadInput = this.viewNode.querySelector( "input[type=file]" );

    var self = this;

    if ( this.supportsHtml5Uploads() )
    {
        this.createUploadProgressIndicatorContainer();

        this.uploadInput.addEventListener("change", function ()
        {
            self.filesSelected( this.files );
        }, false );
    }
};

/**
 * Should create the container used for appending upload progress indicators.
 */
bridge.prototype.createUploadProgressIndicatorContainer = function()
{
    this.uploadProgressIndicatorContainer = document.createElement( "div" );
    this.uploadProgressIndicatorContainer.className = "upload-progress-container";

    this.viewNode.appendChild( this.uploadProgressIndicatorContainer );
};

bridge.prototype.filesSelected = function( files )
{
    var self = this;

    for (var i = 0, file; file = files[i]; i++)
    {
        var uploadFunction = function( file )
        {
            self.sendFileAsServerEvent( "FileUploadedXhr", file, function( e )
            {
                var progress =
                {
                    "name": file.name,
                    "position": e.loaded,
                    "length": e.total,
                    "percentage": parseInt( ( e.loaded / e.total ) * 100 )
                };

                if ( !file.uploadProgressDom )
                {
                    file.uploadProgressDom = self.createUploadProgressIndicator();
                    self.attachUploadProgressIndicator( file.uploadProgressDom );
                }

                self.updateUploadProgressIndicator( file.uploadProgressDom, progress );

            }, function( response )
            {
                if ( file.uploadProgressDom )
                {
                    self.onUploadComplete( file.uploadProgressDom );
                }

                self.raiseClientEvent( "UploadComplete", file, response );
            });
        }( file );
    }
};

/**
 * Called to create the DOM for a progress indicator.
 */
bridge.prototype.createUploadProgressIndicator = function()
{
    var upiDom = document.createElement( "div" );
    upiDom.className = "upload-progress";

    var upiGauge = document.createElement( "div" );
    upiGauge.className = "_gauge";

    var upiNeedle = document.createElement( "div" );
    upiNeedle.className = "_needle";

    var upiLabel = document.createElement( "label" );

    upiGauge.appendChild( upiNeedle );

    upiDom.appendChild( upiGauge );
    upiDom.appendChild( upiLabel );

    // Put the sub elements on the parent as direct children for faster access later.
    upiDom.upiNeedle = upiNeedle;
    upiDom.upiLabel = upiLabel;

    return upiDom;
};

bridge.prototype.attachUploadProgressIndicator = function( progressIndicator )
{
    this.uploadProgressIndicatorContainer.appendChild( progressIndicator );
};

/**
 * Updates the DOM for a progress indicator to reflect the progress passed to it.
 *
 * @param progressIndicator the DOM node created in createUploadProgressIndicator
 * @param progressDetails An object containing name, length, position and percentage properties
 */
bridge.prototype.updateUploadProgressIndicator = function( progressIndicator, progressDetails )
{
    progressIndicator.upiNeedle.style.width = progressDetails.percentage + "%";
    progressIndicator.upiLabel.innerHTML = progressDetails.name;
};

/**
 * Called when an upload is complete. Provides an opportunity to remove a progress indicator.
 *
 * @param progressIndicator
 */
bridge.prototype.onUploadComplete = function( progressIndicator )
{
    this.addClass( progressIndicator, "-is-complete" );

    setTimeout( function()
    {
        progressIndicator.parentNode.removeChild( progressIndicator );
    }, 3000 );
};

bridge.prototype.addClass = function(nodes, className)
{
    if (!nodes.length)
    {
        nodes = [nodes];
    }

    for (var n = 0, m = nodes.length; n < m; n++)
    {
        var node = nodes[n];

        if ((" " + node.className + " ").indexOf(" " + className + " ") == -1)
        {
            node.className += " " + className;
        }
    }
};

bridge.prototype.removeClass = function(nodes, className)
{
    if (!nodes.length)
    {
        nodes = [nodes];
    }

    for (var n = 0, m = nodes.length; n < m; n++)
    {
        var node = nodes[n];
        node.className = node.className.replace( className, '').trim();
    }
}

window.gcd.core.mvp.viewBridgeClasses.MultipleHtmlFileUploadViewBridge = bridge;