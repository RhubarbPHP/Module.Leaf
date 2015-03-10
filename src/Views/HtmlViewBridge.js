if ( !window.gcd )
{
    window.gcd = {};
}

if ( !window.gcd.core )
{
    window.gcd.core = {};
}

window.gcd.core.mvp = {};
window.gcd.core.mvp.registeredPresenters = {};
window.gcd.core.mvp.viewBridgeClasses = {};

window.gcd.core.mvp.spawn = function( spawnSettings, viewIndex )
{
	var viewBridgeClass = window.gcd.core.mvp.viewBridgeClasses[ spawnSettings.ViewBridgeClass ];

	if ( viewBridgeClass.spawn )
	{
		var element = viewBridgeClass.spawn( spawnSettings, viewIndex );

		var bridge = new viewBridgeClass( element );

		for( var i in spawnSettings )
		{
			bridge.model[i] = spawnSettings[i];
		}

		return element;
	}

	return false;
};

/**
 * A base class for the client side extension of server side Presenters.
 *
 * This includes low level plumbing for talking through AJAX to the server presenters without
 * using jQuery. By not relying on jQuery we ensure that we aren't going to be upset by
 * jQuery version conflicts and our foot print is smaller.
 *
 * @param presenter Either an ID string or an HTMLElement
 * @constructor
 */
function HtmlViewBridge( presenter )
{
    if ( arguments.length == 0 )
    {
        return;
    }

	if ( typeof presenter == "string" )
	{
		this.presenterPath = presenter;
		this.presenterName = presenter;
		this.viewNode = document.getElementById( this.presenterPath );
	}
	else
	{
		this.viewNode = presenter;

		this.presenterPath = this.viewNode.id;
		this.presenterName = this.viewNode.id;
	}

    // parentViewBridge and containingViewBridge will be set in onParentsReady()
    this.parentViewBridge = false;
    this.containingViewBridge = false;

    if ( this.viewNode )
    {
        if ( this.viewNode.viewBridge )
        {
            // This element already has a viewBridge attached. For some reason this bridge is being
            // constructed a second time for the same
            // return;
        }

        if ( this.viewNode.attributes["presenter-name"] )
        {
            this.presenterName = this.viewNode.attributes["presenter-name"].value;
        }

        this.viewNode.viewBridge = this;
    }

    if ( presenter == "host" )
    {
        this.presenterPath = this.presenterName;
    }

	var path = this.presenterPath;
	var suffix = "";

    try
    {
        this.presenterPhpClass = document.getElementById(  path + "Class" ).value;
    }
    catch( exception ){}

    try
    {
		this.presenterUrl = document.getElementById( path + "Url" ).value;
    }
    catch( exception ){}

    this.serverEventResponseHandlers = {};

    this.clientEventHandlers = {};
    this.model = [];

	if( this.viewNode )
	{
		this.host = ( this.viewNode.className.indexOf( "host" ) > -1 );
	}

	this.loadState();

	this.eventHostClassName = "";

	if ( document.getElementById( this.presenterPath + 'EventHost' ) )
	{
		this.eventHostClassName = document.getElementById( this.presenterPath + 'EventHost' ).value;
		this.host = true;
	}

    this.registerPresenter();

    if ( this.viewNode && this.viewNode.serverEventResponseHandlers )
    {
        this.serverEventResponseHandlers = this.viewNode.serverEventResponseHandlers;
    }

    this.attachDomChangeEventHandler();
}

HtmlViewBridge.prototype.hasValue = function()
{
	if( !this.viewNode )
	{
		return false;
	}

	var hasValue = false;
	var nodeTagName = this.viewNode.tagName.toLowerCase();
	if( nodeTagName == "input" || nodeTagName == "select" || nodeTagName == "textarea" )
	{
		hasValue = true;

		if( this.viewNode.type && ( this.viewNode.type.toLowerCase() == "button" ||
			this.viewNode.type.toLowerCase() == "submit" ||
			this.viewNode.type.toLowerCase() == "image" ) )
		{
			hasValue = false;
		}
	}
	return hasValue;
};

/**
 * Override to attach the DOM listeners required so you can call the valueChanged() method.
 */
HtmlViewBridge.prototype.attachDomChangeEventHandler = function( triggerChangeEvent )
{
	if ( !triggerChangeEvent )
	{
		triggerChangeEvent = false;
	}

	var self = this;

	var callBack = function()
	{
		self.valueChanged();
	};

	if (!this.viewNode.addEventListener)
	{
		this.viewNode.attachEvent( "onchange", callBack );
	}
	else
	{
		// Be interested in a changed event if there is one.
		this.viewNode.addEventListener( 'change', callBack , false );
	}

	if ( triggerChangeEvent )
	{
		callBack();
	}
};

HtmlViewBridge.prototype.getViewIndex = function()
{
	var pattern = /\[_([^\]]+)\]$/;

	var match = pattern.exec( this.viewNode.id );

	if ( match )
	{
		return match[1];
	}

	return false;
};

/**
 * A static function to allow creation of a view bridge entirely from data provided
 *
 * This will return a DOMElement that can be inserted into the DOM just as if it always existed.
 *
 * @param spawnData
 * @param [index]
 */
HtmlViewBridge.spawn = function( spawnData, index )
{

};

/**
 * Sets common attributes on a newly spanned view bridge such as id, name
 * and presenter-name.
 *
 * @param spawnData
 * @param node
 * @param [index]
 */
HtmlViewBridge.applyStandardAttributesToSpawnedElement = function( node, spawnData, index )
{
	var id = spawnData.PresenterPath;

	if ( index !== null && index !== false && (typeof index !== "undefined") )
	{
		id += "(" + index + ")";
	}

	node.id = id;
	node.setAttribute( "name", id );
	node.setAttribute( "presenter-name", spawnData.PresenterName );
};

HtmlViewBridge.prototype.registerPresenter = function()
{
	window.gcd.core.mvp.registeredPresenters[ this.presenterPath ] = this;

    this.onRegistered();
    this.attachEvents();

	for( var path in window.gcd.core.mvp.registeredPresenters )
	{
		var presenter = window.gcd.core.mvp.registeredPresenters[ path ];

		if ( path.indexOf( '_' ) == -1 )
		{
			// This is a root presenter and as such, all children should now be registered
			// We now inform them all of this situation so they can determine their event host.

			for( var subPath in window.gcd.core.mvp.registeredPresenters )
			{
				if ( ( subPath != path ) && ( subPath.indexOf( path ) == 0 ) )
				{
					window.gcd.core.mvp.registeredPresenters[ subPath ].onParentsReady();
				}
			}
		}
	}
};

HtmlViewBridge.prototype.onReattached = function()
{

};


HtmlViewBridge.prototype.onRegistered = function()
{

};

HtmlViewBridge.prototype.onParentsReady = function()
{
	if ( !this.eventHost )
	{
    	this.eventHost = this.findEventHost();
	}

	if ( !this.parentViewBridge )
	{
    	this.parentViewBridge = this.findParent();
	}
};

HtmlViewBridge.prototype.getContainingViewBridge = function()
{
	if ( !this.containingViewBridge )
	{
		this.containingViewBridge = this.findContainingViewBridge();
	}

	return this.containingViewBridge;
};

HtmlViewBridge.prototype.findContainingViewBridge = function()
{
    var parent = this.viewNode.parentNode;

    while( parent )
    {
        if ( parent.viewBridge )
        {
            return parent.viewBridge;
        }

        parent = parent.parentNode;
    }

    return false;
};

HtmlViewBridge.prototype.findParent = function()
{
    var presenterPaths = [];

    for( var i in window.gcd.core.mvp.registeredPresenters )
    {
        presenterPaths[ presenterPaths.length ] = i;
    }

    presenterPaths.sort();

    for( var i in presenterPaths )
    {
        var presenter = window.gcd.core.mvp.registeredPresenters[ presenterPaths[ i ] ];

        // Check for a parent by subtracting the test presenter name away from this one.
        // The remainder should have no underscores.
        var residualPath = this.presenterPath.replace( presenter.presenterPath + "_", '' );

        if ( residualPath.indexOf( "_" ) == -1 )
        {
            return presenter;
        }
    }
};

HtmlViewBridge.prototype.attachServerEventResponseHandlerTo = function( domElement, event, callback )
{
    if ( domElement.viewBridge )
    {
        domElement.HtmlViewBridge.attachServerEventResponseHandler( event, callback );
    }
    else
    {
        if ( !domElement.serverEventResponseHandlers )
        {
            domElement.serverEventResponseHandlers = {};
        }

        if ( !domElement.serverEventResponseHandlers[ event ] )
        {
            domElement.serverEventResponseHandlers[ event ] = [];
        }

        domElement.serverEventResponseHandlers[ event ][ domElement.serverEventResponseHandlers[ event ].length ] = callback;
    }
};

/**
 * Searches with the inner DOM of the viewBridge looking for a sub viewBridge with the matching name.
 *
 * This differs from findViewBridge in that it must be a direct child of the container. In other words
 * grand children or further removed descendants would not match.
 *
 * @param presenterName
 * @param [viewIndex] If you're looking for an indexed view bridge, you'll need to pass it's index here. If you don't
 * 					  you'll get the first it comes across.
 */
HtmlViewBridge.prototype.findChildViewBridge = function( presenterName, viewIndex )
{
	var presenterPaths = [];

	for( var i in window.gcd.core.mvp.registeredPresenters )
	{
		presenterPaths[ presenterPaths.length ] = i;
	}

	presenterPaths.sort();

	var thisPresenterPath = this.presenterPath + '_';

	for( var i in presenterPaths )
	{
		var presenter = window.gcd.core.mvp.registeredPresenters[ presenterPaths[ i ] ];

		if( presenter.presenterName == presenterName )
		{
			var presenterPath = presenter.presenterPath;

			// Check the viewBridge we're considering is a child of this one.
			if( presenterPath.indexOf( thisPresenterPath ) == 0 )
			{
				if( presenterPath.replace( thisPresenterPath, '' ).indexOf( "_" ) == -1 )
				{
					return presenter;
				}
			}
		}
	}

	return false;
};

/**
 * Searches with the inner DOM of the viewBridge looking for a sub viewBridge with the matching name
 *
 * @param presenterName
 * @param [viewIndex]	If you're looking for an indexed view bridge, you'll need to pass its index here. If you don't
 * 					you'll get the first it comes across.
 */
HtmlViewBridge.prototype.findViewBridge = function( presenterName, viewIndex )
{
	var presenterPaths = [];

	for( var i in window.gcd.core.mvp.registeredPresenters )
	{
		presenterPaths[ presenterPaths.length ] = i;
	}

	presenterPaths.sort();

	var thisViewIndex = this.getViewIndex();
	var thisPresenterPath = this.presenterPath + '_';

    for( var i in presenterPaths )
    {
        var presenter = window.gcd.core.mvp.registeredPresenters[ presenterPaths[ i ] ];

        if ( presenter.presenterName == presenterName )
        {
			// This viewBridge is indexed, so check the viewBridge we're considering matches this one's index
			// Check the viewBridge we're considering is a child of this one.
            if( presenter.presenterPath.indexOf( thisPresenterPath ) == 0 )
            {
				return presenter;
			}
		}
	}

	return false;
};

HtmlViewBridge.prototype.clearServerEventResponseHandlers = function( event )
{
	this.serverEventResponseHandlers = {};

};

HtmlViewBridge.prototype.attachServerEventResponseHandler = function( event, callback )
{
    if ( !this.serverEventResponseHandlers[ event ] )
    {
        this.serverEventResponseHandlers[ event ] = [];
    }

    this.serverEventResponseHandlers[ event ][ this.serverEventResponseHandlers[ event ].length ] = callback;
};

/**
 * Attaches a callback to be triggered when an event is raised on the client.
 *
 * This is raised for events that are triggered using raiseServerEvent however this callback is
 * triggered first, before the server is passed the event.
 *
 * @param event
 * @param callback
 */
HtmlViewBridge.prototype.attachClientEventHandler = function( event, callback )
{
    if ( !this.clientEventHandlers[ event ] )
    {
        this.clientEventHandlers[ event ] = [];
    }

    this.clientEventHandlers[ event ][ this.clientEventHandlers[ event ].length ] = callback;
};

HtmlViewBridge.prototype.removeClientEventHandler = function( event, callback )
{
	if( !this.clientEventHandlers[ event ] )
	{
		return;
	}

	var index = this.clientEventHandlers[ event ].indexOf( callback );
	if( index != -1 )
	{
		this.clientEventHandlers[ event ].splice( index, 1 );
	}
};

HtmlViewBridge.prototype.removeClientEventHandlers = function( event )
{
	this.clientEventHandlers[ event ] = [];
};

/**
 * Loads the state of the viewBridge model
 */
HtmlViewBridge.prototype.loadState = function()
{
	var viewIndex = this.getViewIndex();
	var path = this.presenterPath;

    if ( !document.getElementById( path + 'State' ) || ( document.getElementById( path + 'State' ).value == '' ) )
    {
        return;
    }

    this.model = eval( '(' + document.getElementById( path + 'State' ).value + ')' );

    if ( document.getElementById( this.presenterPath ) )
    {
        if ( document.getElementById( this.presenterPath).className == "host" )
        {
            this.host = true;
        }
    }

	this.onStateLoaded();
};

/**
 * Loads the state of the viewBridge model
 */
HtmlViewBridge.prototype.saveState = function()
{
    if ( !document.getElementById( this.presenterPath + 'State' ) )
    {
        return;
    }

    document.getElementById( this.presenterPath + 'State' ).value = JSON.stringify( this.model );
};

HtmlViewBridge.prototype.onStateLoaded = function()
{

};

HtmlViewBridge.prototype.getSubPresenters = function()
{
    var subPresenters = [];

    for( var subPath in window.gcd.core.mvp.registeredPresenters )
    {
        if ( subPath == this.presenterPath )
        {
            // We are not a child of ourselves
            continue;
        }

        if ( subPath.indexOf( this.presenterPath ) == 0 )
        {
            subPresenters[ subPresenters.length ] = window.gcd.core.mvp.registeredPresenters[ subPath ];
        }
    }

    return subPresenters;
};

HtmlViewBridge.prototype.onSubPresenterValueChanged = function()
{

};

HtmlViewBridge.prototype.subPresenterValueChanged = function( viewBridge, newValue )
{
    this.onSubPresenterValueChanged.apply( this, arguments );

	var container = this.getContainingViewBridge();

    if ( container )
    {
        container.subPresenterValueChanged( viewBridge, newValue );
    }
};

HtmlViewBridge.prototype.valueChanged = function()
{
    var newValue = this.getValue();

	var container = this.getContainingViewBridge();

    if ( container )
    {
		container.subPresenterValueChanged( this, newValue );
    }

    this.raiseClientEvent( "ValueChanged", this, newValue );
};

/**
 * Returns value for the viewBridge if appropriate.
 *
 * Used to build models for client side validation.
 *
 * @returns {string}
 */
HtmlViewBridge.prototype.getValue = function()
{
	if ( this.viewNode && this.viewNode.value )
	{
		return this.viewNode.value;
	}

    return "";
};

HtmlViewBridge.prototype.getDisplayView = function()
{
	return this.getValue();
};

HtmlViewBridge.prototype.setValue = function( value )
{
	if ( this.viewNode && ( "value" in this.viewNode ) )
	{
		this.viewNode.value = value;
	}
};

HtmlViewBridge.prototype.getSubPresenterValues = function()
{
	// Get all the values from all the sub presenters to build our model to validate.
	var subPresenters = this.getSubPresenters();
	var model = {};

	for( var i in subPresenters )
	{
		var subPresenter = subPresenters[i];

		model[ subPresenter.presenterName ] = subPresenter.getValue();
	}

	return model;
};

HtmlViewBridge.prototype.validate = function( validator )
{
    var model = this.getSubPresenterValues();

    try
    {
        validator.validate( model );
    }
    catch( error )
    {
        var placeholders = document.getElementsByTagName( "em" );

        for( var i = 0; i < placeholders.length; i++ )
        {
            if ( placeholders[ i ].className.indexOf( "validation-placeholder" ) > -1 )
            {
                placeholders[ i ].innerHTML = "";
            }
        }

        // For now we simply try and update any matching placeholders with the relevant error.
        error.applyToPlaceholders( this.viewNode );

        return error;
    }

    return true;
};

/**
 * Override this to attach any event handlers.
 *
 * Called once the state has been restored.
 */
HtmlViewBridge.prototype.attachEvents = function()
{

};

/**
 * Searches through the parents of the viewBridge to find the host viewBridge element.
 *
 * @return {*}
 */
HtmlViewBridge.prototype.findEventHost = function()
{
    var selfNode = document.getElementById( this.presenterPath );

    while( selfNode )
    {
        var testNode = selfNode;

        selfNode = selfNode.parentNode;

        var className = ( testNode.className ) ? testNode.className : "";

        if ( className.indexOf( "host" ) == 0 || className.indexOf( "host" ) > 0 )
        {
            if ( !testNode.viewBridge )
            {
                if ( !testNode.id )
                {
                    testNode.id = "host";
                }

                new window.HtmlViewBridge( testNode.id );

                if ( testNode.className.indexOf( "host" ) == 0 || testNode.className.indexOf( "host" ) > 0 )
                {
                    testNode.viewBridge.host = true;
                }
            }
        }

        if ( testNode.viewBridge && testNode.viewBridge.host && testNode.className.indexOf( "configured" ) == -1  )
        {
            return testNode.viewBridge;
        }
    }

    return false;
};

/**
 * Raises an event for consumption only by listeners on the client.
 *
 * @param eventName
 */
HtmlViewBridge.prototype.raiseClientEvent = function( eventName )
{
    if ( !this.clientEventHandlers[ eventName ] )
    {
        return;
    }

    var argumentsArray = [];

    for( var i = 1; i < arguments.length; i++ )
    {
        argumentsArray[ i - 1 ] = arguments[ i ];
    }

	var lastResponse = null;

    for( var i in this.clientEventHandlers[ eventName ] )
    {
        var callback = this.clientEventHandlers[ eventName ][ i ];
		lastResponse = callback.apply( callback, argumentsArray );
    }

	return lastResponse;
};


HtmlViewBridge.prototype.sendFileAsServerEvent = function( eventName, file, onProgress, onComplete )
{
    if ( !this.eventHost )
    {
        this.eventHost = this.findEventHost();
    }

    // If we're not the host we need to find the host and call it's raise event instead.
    var hostPresenter = this.eventHost;
    var self = this;

    var xmlhttp = this.createXmlHttpRequest();
    var presenter = this;

    xmlhttp.upload.onprogress = onProgress;

    // Attach the call back wrapper for the AJAX post.
    xmlhttp.onreadystatechange = function()
    {
        if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 )
        {
            document.body.className = document.body.className.replace( " event-processing", "" );
			presenter.onEventProcessingFinished();

			if ( xmlhttp.responseXML != null )
            {
                self.parseEventResponse( eventName, xmlhttp.responseXML, onComplete );
            }
        }
    };

    var target = this.presenterPath;
    var index = this.getViewIndex();

    if ( index )
    {
        target = target.replace( "[_" + index + "]", "" );
    }

    if( hostPresenter )
    {
        var formData = new FormData();

        formData.append( "_mvpEventName", eventName );
        formData.append( "_mvpEventTarget", target );

        if ( index )
        {
            formData.append( "_mvpEventTargetIndex", index );
        }

        if ( hostPresenter.eventHostClassName != "" )
        {
            formData.append( "_mvpEventClass", hostPresenter.eventHostClassName );
            formData.append( "_mvpEventPresenterPath", hostPresenter.presenterPath );
        }

        formData.append( this.presenterPath , file );

        var ajaxUrl = "";

        if ( hostPresenter.presenterUrl )
        {
            ajaxUrl = hostPresenter.presenterUrl;
        }
        else
        {
            ajaxUrl = hostPresenter.presenterPhpClass.replace( /\\/g, "/" );
        }

        xmlhttp.open( "POST", ajaxUrl, true );
        xmlhttp.setRequestHeader( 'Accept', 'application/core' );
        xmlhttp.setRequestHeader( 'X-Requested-With', 'xmlhttprequest' );
        xmlhttp.send( formData );

        document.body.className = document.body.className + " event-processing";
		presenter.onEventProcessingStarted();
    }

    return xmlhttp;
};

HtmlViewBridge.prototype.raisePostBackEvent = function( eventName )
{
	var argumentsArray = [];
	var callback = false;

	// Get the arguments into a proper array while stripping any closure found to become a callback.

	for( var i = 0; i < arguments.length; i++ )
	{
		argumentsArray[ i ] = arguments[ i ];

		if ( arguments[i] instanceof Function )
		{
			callback = arguments[ i ];
		}
	}

	// Give the client side a first look at the event.
	this.raiseClientEvent.apply( this, argumentsArray );

	// Standardise the arguments list by ensuring the targeted viewBridge is the last parameter.

	if ( argumentsArray[ argumentsArray.length - 1 ] instanceof HtmlViewBridge )
	{
		targetHtmlViewBridge = argumentsArray[ argumentsArray.length - 1 ];
	}
	else
	{
		targetHtmlViewBridge = this;
		argumentsArray[ argumentsArray.length ] = targetHtmlViewBridge;
	}

	if ( !this.eventHost )
	{
		this.eventHost = this.findEventHost();
	}

	// If we're not the host we need to find the host and call it's raise event instead.
	var hostPresenter = this.eventHost;

	var presenter = this;

	var target = targetHtmlViewBridge.presenterPath;
	var index = targetHtmlViewBridge.getViewIndex();

	if ( index )
	{
		target = target.replace( "[_" + index + "]", "" );
	}

	if( hostPresenter )
	{
		var createOrFindHiddenInput = function( inputName ){

			if ( document.getElementById( inputName ) ){
				return document.getElementById( inputName );
			} else {
				var newInput = document.createElement( 'input' );
				newInput.type = "hidden";
				newInput.id = inputName;
				newInput.name = inputName;
				hostPresenter.viewNode.appendChild( newInput );

				return newInput;
			}
		};

		var eventNameInput = createOrFindHiddenInput( "_mvpEventName");
		var eventTargetInput = createOrFindHiddenInput( "_mvpEventTarget");
		var eventTargetIndexInput = createOrFindHiddenInput( "_mvpTargetIndex");
		var eventClassInput = createOrFindHiddenInput( "_mvpClass");
		var eventPresenterPathInput = createOrFindHiddenInput( "_mvpPresenterPath");
		var eventArgumentsInput = createOrFindHiddenInput( "_mvpEventArgumentsJson");

		eventNameInput.value = eventName;
		eventTargetInput.value = target;

		if ( index )
		{
			eventTargetIndexInput.value = index;
		}

		if ( hostPresenter.eventHostClassName != "" )
		{
			eventClassInput.value = hostPresenter.eventHostClassName;
			eventPresenterPathInput.value = hostPresenter.presenterPath;
		}

		var flatArguments = [];

		for( var i = 1; i < arguments.length; i++ )
		{
			var argument = arguments[ i ];

			if ( !(argument instanceof HtmlViewBridge ) && !( argument instanceof Function ) )
			{
				flatArguments.push( argument );
			}
		}

		eventArgumentsInput.value = JSON.stringify( flatArguments );

		// Our parent should be the form tag.
		hostPresenter.viewNode.parentNode.submit();
	}
};

/**
 * Raises an event via an XMLHttpRequest
 *
 * If this is not the host viewBridge we bubble the event up to the host HtmlViewBridge.
 *
 * @param eventName The name of the event to trigger
 * @param targetHtmlViewBridge The name of the viewBridge the event is being triggered for
 */
HtmlViewBridge.prototype.raiseServerEvent = function( eventName )
{
    var argumentsArray = [];
    var callback = false;

    // Get the arguments into a proper array while stripping any closure found to become a callback.

    for( var i = 0; i < arguments.length; i++ )
    {
        argumentsArray[ i ] = arguments[ i ];

        if ( arguments[i] instanceof Function )
        {
            callback = arguments[ i ];
        }
    }

    // Give the client side a first look at the event.
    this.raiseClientEvent.apply( this, argumentsArray );

    // Standardise the arguments list by ensuring the targeted viewBridge is the last parameter.

    if ( argumentsArray[ argumentsArray.length - 1 ] instanceof HtmlViewBridge )
    {
        targetHtmlViewBridge = argumentsArray[ argumentsArray.length - 1 ];
    }
    else
    {
        targetHtmlViewBridge = this;
        argumentsArray[ argumentsArray.length ] = targetHtmlViewBridge;
    }

    if ( !this.eventHost )
    {
        this.eventHost = this.findEventHost();
    }

    // If we're not the host we need to find the host and call it's raise event instead.
    var hostPresenter = this.eventHost;

    var xmlhttp = this.createXmlHttpRequest();
    var presenter = this;

    // Attach the call back wrapper for the AJAX post.

    xmlhttp.onreadystatechange = function()
    {
        if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 )
        {
            document.body.className = document.body.className.replace( " event-processing", "" );

            if ( xmlhttp.responseXML != null )
            {
                targetHtmlViewBridge.parseEventResponse( eventName, xmlhttp.responseXML, callback );
            }
        }
    };

    var target = targetHtmlViewBridge.presenterPath;
    var index = targetHtmlViewBridge.getViewIndex();

    if ( index )
    {
        target = target.replace( "[_" + index + "]", "" );
    }
	if( hostPresenter )
	{
		var formData = hostPresenter.findInputsAndSerialize( hostPresenter.viewNode );

		formData += "_mvpEventName=" + eventName + "&_mvpEventTarget=" + target;

		if ( index )
		{
			formData += "&_mvpEventTargetIndex=" + index;
		}

		if ( hostPresenter.eventHostClassName != "" )
		{
			formData += "&_mvpEventClass=" + hostPresenter.eventHostClassName + "&_mvpEventPresenterPath=" + hostPresenter.presenterPath;
		}

		for( var i = 1; i < arguments.length; i++ )
		{
			var argument = arguments[ i ];

			if ( !(argument instanceof HtmlViewBridge ) && !( argument instanceof Function ) )
			{
				argument = JSON.stringify( argument );
				formData += "&_mvpEventArguments[]=" + encodeURIComponent( argument );
			}
		}

		var ajaxUrl = "";

		if ( hostPresenter.presenterUrl )
		{
			ajaxUrl = hostPresenter.presenterUrl;
		}
		else
		{
			ajaxUrl = hostPresenter.presenterPhpClass.replace( /\\/g, "/" );
		}

		xmlhttp.open( "POST", ajaxUrl, true );
		xmlhttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
		xmlhttp.setRequestHeader( 'Accept', 'application/core' );
		xmlhttp.setRequestHeader( 'X-Requested-With', 'xmlhttprequest' );
		xmlhttp.send( formData );

		document.body.className += " event-processing";
		targetHtmlViewBridge.onEventProcessingStarted();
	}

	return xmlhttp;
};

HtmlViewBridge.prototype.onEventProcessingStarted = function()
{
	if ( this.viewNode )
	{
		this.viewNode.className += " my-event-processing";
	}
};

HtmlViewBridge.prototype.onEventProcessingFinished = function()
{
	if ( this.viewNode )
	{
		this.viewNode.className = this.viewNode.className.replace( " my-event-processing", "" );
	}
};

/**
 * Creates a new XMLHttpRequest object
 *
 * Provides an opportunity to configure the XMLHttpRequest object if required.
 *
 * @returns {XMLHttpRequest}
 */
HtmlViewBridge.prototype.createXmlHttpRequest = function()
{
    return new XMLHttpRequest();
};

HtmlViewBridge.prototype.loadJson = function( url, callback )
{
	var xmlhttp = new XMLHttpRequest();

	xmlhttp.onreadystatechange = function()
	{
		if ( xmlhttp.readyState == 4 && xmlhttp.status == 200 )
		{
			callback( JSON.parse( xmlhttp.responseText ) );
		}
	};

	xmlhttp.open( "GET", url, true );
	xmlhttp.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	xmlhttp.setRequestHeader( 'Accept', 'application/json' );
	xmlhttp.setRequestHeader( 'X-Requested-With', 'xmlhttprequest' );
	xmlhttp.send();
};

/**
 * Parses the raw xml response from the AJAX postback.
 *
 * We look both for <htmlupdate> tags and update the relevant elements and
 * <eventresponse> tags to call event handlers on the client.
 *
 * @param eventName
 * @param responseXml
 */
HtmlViewBridge.prototype.parseEventResponse = function( eventName, responseXml, callback )
{
    var updateElements = responseXml.getElementsByTagName( "htmlupdate" );
    var eventResponses = responseXml.getElementsByTagName( "eventresponse" );
    var scripts = responseXml.getElementsByTagName( "script" );
	var models = responseXml.getElementsByTagName( "model" );
	var eventsToRaise = responseXml.getElementsByTagName( "event" );

    for( var i = 0; i < updateElements.length; i++ )
    {
        var element = updateElements[ i ];
        var targetId = element.getAttribute( "id" );
        var content = ( element.textContent ) ? element.textContent : element.text;

        var targetElement = document.getElementById( targetId );

        if ( targetElement )
        {
			if( targetElement.viewBridge )
			{
				targetElement.viewBridge.onBeforeUpdateDomUpdateFromServer();
			}

            var shim = document.createElement( "div" );
            shim.innerHTML = content;

            var realContent = shim.children[0].innerHTML;

            targetElement.innerHTML = realContent;

            if ( targetElement.viewBridge )
            {
                targetElement.viewBridge = undefined;

                try
                {
                    delete targetElement.viewBridge;
                }
                catch( e ){}
            }
        }
    }

	for( var i = 0; i < models.length; i++ )
	{
		var model = models[i];
		var target = model.getAttribute( "id" );
		var content = ( model.textContent ) ? model.textContent : model.text;

		var stateElement = document.getElementById( target + "State" );

		if ( stateElement )
		{
			stateElement.value = content;
		}

		var viewNode = document.getElementById( target );

		if( viewNode && viewNode.viewBridge )
		{
			viewNode.viewBridge.modelUpdatedByEvent();
		}
	}

    var callBackCalled = false;

    if ( eventResponses.length > 0 )
    {
        var eventResponse = eventResponses[0];

        if ( eventResponse.getAttribute( "event" ) == eventName )
        {
            var response = ( eventResponse.textContent ) ? eventResponse.textContent : eventResponse.text;

            if ( eventResponse.getAttribute( "type" ) == "json" )
            {
                response = eval( "(" + response + ")" );
            }
            else
            {
                response = response.trim();
            }

            this.onServerEventResponseReceived( eventName, response );

            if ( callback )
            {
                callback( response );
                callBackCalled = true;
            }
        }
    }

    if ( !callBackCalled && callback )
    {
        callback();
    }

    if ( scripts.length > 0 )
    {
        for( var i = 0; i < scripts.length; i++ )
        {
            var script = scripts[i];

            try
            {
                eval( script.textContent );
            }
            catch( exception )
            {

            }
        }
    }

    this.reAttachHtmlViewBridges();

	for( var i = 0; i < eventsToRaise.length; i++ )
	{
		var event = eventsToRaise[ i ];

		var name = event.getAttribute( "name" );
		var target = event.getAttribute( "target" );

		var eventParams = [ name ];

		for( var c = 0; c < event.childNodes.length; c++ )
		{
			var paramNode = event.childNodes[c];

			eventParams.push( paramNode.textContent );
		}

		if ( window.gcd.core.mvp.registeredPresenters[ target ] )
		{
			var registeredPresenter = window.gcd.core.mvp.registeredPresenters[ target ];
			registeredPresenter.raiseClientEvent.apply( registeredPresenter, eventParams );
		}
	}
};

/**
 * Override this method to perform anything required before the DOM is updated
 */
HtmlViewBridge.prototype.onBeforeUpdateDomUpdateFromServer = function()
{

};

HtmlViewBridge.prototype.modelUpdatedByEvent = function()
{
	this.loadState();
	this.onModelUpdatedByEvent();
};

/**
 * Override this to handle detection of changes to the public model passed back from the server.
 */
HtmlViewBridge.prototype.onModelUpdatedByEvent = function()
{
};

HtmlViewBridge.prototype.reAttachHtmlViewBridges = function()
{

    for ( var path in window.gcd.core.mvp.registeredPresenters )
    {
        var presenter = window.gcd.core.mvp.registeredPresenters[ path ];
        var viewNode = document.getElementById( path );

        if ( viewNode )
        {
            if ( !viewNode.viewBridge )
            {
                presenter.viewNode = viewNode;
                viewNode.viewBridge = presenter;

				presenter.onReattached();
                presenter.onRegistered();
				presenter.attachDomChangeEventHandler( true );
                presenter.attachEvents();
            }
        }
    }

    for( var path in window.gcd.core.mvp.registeredPresenters )
    {
        if ( path.indexOf( '_' ) == -1 )
        {
            // This is a root viewBridge and as such, all children should now be registered
            // We now inform them all of this situation so they can determine their event host.
            for( var subPath in window.gcd.core.mvp.registeredPresenters )
            {
                if ( ( subPath != path ) && ( subPath.indexOf( path ) == 0 ) )
                {
                    window.gcd.core.mvp.registeredPresenters[ subPath ].onParentsReady();
                }
            }
        }
    }
};

/**
 * Search the container for inputs and serialize their input values.
 *
 * @param containingDiv
 * @return {String}
 */
HtmlViewBridge.prototype.findInputsAndPopulate = function( data )
{
	var subPresenters = this.getSubPresenters();

	for( var i in subPresenters )
	{
		var subPresenter = subPresenters[i];

		if ( !subPresenter.hasValue() )
		{
			continue;
		}

		if ( data[ subPresenter.presenterName ] !== undefined )
		{
			subPresenter.setValue( data[ subPresenter.presenterName ] );
		}
	}
};

/**
 * Search the container for inputs and serialize their input values.
 *
 * @param containingDiv
 * @return {String}
 */
HtmlViewBridge.prototype.findInputsAndSerialize = function( containingDiv )
{
	var subPresenters = this.getSubPresenters();
	var serialString = "";

	for( var i in subPresenters )
	{
		var subPresenter = subPresenters[i];

		if ( !subPresenter.hasValue() )
		{
			continue;
		}

		var value = subPresenter.getValue();
		if( typeof value == "object" )
		{
			for( var j in value )
			{
				serialString += subPresenter.presenterPath + "[]=" + encodeURIComponent( value[j] ) + "&";
			}
		}
		else if( typeof value == "boolean" )
		{
			serialString += subPresenter.presenterPath + "=" + ( ( value ) ? "1" : "0" ) + "&";
		}
		else
		{
        	serialString += subPresenter.presenterPath + "=" + encodeURIComponent( value ) + "&";
		}
    }

	// Add all hidden inputs on the page
	var inputs = containingDiv.getElementsByTagName( "input" );

	for( var i = 0; i < inputs.length; i++ )
	{
		var input = inputs[i];
		var type = input.type;

		if ( type.toLowerCase() == "hidden" )
		{
			serialString += input.name + "=" + encodeURIComponent( input.value ) + "&";
		}
	}

    return serialString;
};

HtmlViewBridge.prototype.addElementsToArray = function( elementCollection, inputArray )
{
    for( var i = 0; i < elementCollection.length; i++ )
    {
        inputArray.push( elementCollection[ i ] );
    }

    return inputArray;
};

/**
 * Triggers any attached event handlers for a given event name passing the response
 * from the server.
 *
 * @param eventName
 * @param response
 */
HtmlViewBridge.prototype.triggerServerEventResponseHandlers = function( eventName, response )
{
    if ( !this.serverEventResponseHandlers[ eventName ] )
    {
        return;
    }

    for( var i in this.serverEventResponseHandlers[ eventName ] )
    {
        var callback = this.serverEventResponseHandlers[ eventName ][ i ];

        callback( response );
    }
};

/**
 * Called by parseEventResponse() for any matching event responses
 *
 * @param eventName
 * @param responseText
 * @return {*}
 */
HtmlViewBridge.prototype.onServerEventResponseReceived = function( eventName, responseText )
{
    this.triggerServerEventResponseHandlers( eventName, responseText );

    if ( this[ eventName + "ResponseReceived" ] )
    {
        return this[ eventName + "ResponseReceived" ]( responseText );
    }

    return null;
};

HtmlViewBridge.prototype.setFocus = function()
{
    if ( this.viewNode.focus )
    {
        this.viewNode.focus();
    }
};

/**
 * Returns a list of presenters on the page by viewBridge name.
 *
 * Note that the containingPresenter parameter is optional. Omitting it will do a global search.
 */

window.gcd.core.mvp.waitForPresenters = function( presenterNames, callback, containingPresenter )
{
    var allReady = true;

    for( var i = 0; i < presenterNames.length; i++ )
    {
        var dom = presenterNames[ i ];

		if ( typeof dom == "string" || ( typeof dom == "object" && dom.constructor === String ) )
		{
			// The item passed was the name of the viewBridge node, not the node itself.
			var presenters = window.gcd.core.mvp.getPresentersByName( dom, containingPresenter );

			if ( presenters.length == 0 )
			{
				allReady = false;
				break;
			}
		}
		else
		{
			if ( !dom.viewBridge )
			{
				allReady = false;
				break;
			}
		}
    }

    if ( !allReady )
    {
        setTimeout( function()
        {
            window.gcd.core.mvp.waitForPresenters( presenterNames, callback, containingPresenter );
        }, 100 );
    }
    else
    {
	    var presenters = window.gcd.core.mvp.getPresentersByName( presenterNames, containingPresenter )

        callback.apply( this, presenters );
    }
};

/**
 * Gets an array of presenters having one of the given names.
 *
 * @param presenterNames An array of names or a single name string.
 * @param containingPresenter Optional: Restricts the search to children of a given viewBridge.
 * @returns {Array}
 */
window.gcd.core.mvp.getPresentersByName = function( presenterNames, containingPresenter )
{
	if( typeof presenterNames == "string" )
	{
		presenterNames = [presenterNames];
	}

	var matchedPresenters = [];

	var containingPresenterPath = containingPresenter.presenterPath;

	containingPresenterPath += '_';

	for( var i = 0; i < presenterNames.length; i++ )
	{
		var presenterName = presenterNames[i];

		if ( presenterName.viewBridge )
		{
			// Someone passed an HTML dom element instead of a presenter name. This is provided for
			// as the same trick can be used with waitForPresenters and it calls this method to do
			// most of the heavy lifting.

			matchedPresenters.push( presenterName.viewBridge );
			continue;
		}

		for( var p in window.gcd.core.mvp.registeredPresenters )
		{
			var registeredPresenter = window.gcd.core.mvp.registeredPresenters[p];

			if( registeredPresenter.presenterPath == containingPresenter.presenterPath )
			{
				continue;
			}

			if( registeredPresenter.presenterName == presenterName )
			{
				if( containingPresenter )
				{
					// We must be a parent of this viewBridge
					if( registeredPresenter.presenterPath.indexOf( containingPresenterPath ) != 0 )
					{
						continue;
					}
				}

				matchedPresenters[ matchedPresenters.length ] = registeredPresenter;

				break;
			}
		}
	}

	return matchedPresenters;
};

HtmlViewBridge.prototype.waitForPresenters = function( presenters, callback )
{
	return window.gcd.core.mvp.waitForPresenters( presenters, callback, this );
};

if ( !String.prototype.trim )
{
    String.prototype.trim = function(){return this.replace(/^\s+|\s+$/g, '');};
}

window.gcd.core.mvp.viewBridgeClasses.HtmlViewBridge = HtmlViewBridge;