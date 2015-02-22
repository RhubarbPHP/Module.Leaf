
var searchControl = function( presenterPath )
{
    if ( arguments.length )
    {
        // Construct the search interface first so the when attachEvents is called, we have elements to attach to.
        this.createDom();
    }

    window.gcd.core.mvp.viewBridgeClasses.SelectionControlViewBridge.apply( this, arguments );

	this.supportsMultipleSelection = false;

    if ( arguments.length )
    {
        // Attach interface
        this.element.after( this.interfaceContainer );

		var count = 0;

        if ( this.model.SelectedItems )
        {
			for( var value in this.model.SelectedItems )
			{
				count++;
			}
		}

		if ( count )
		{
            // Simulate selecting an item to ensure all other UI elements
            // update consistently between first page load and subsequent
            // selects.
            this.setSelectedItems( this.model.SelectedItems );
        }
        else
        {
            // Valid states will be:
            // unselected
            // searching
            // searched
            // selected
            // not-searching is special and will change the state to unselected or selected based on whether a value
            // has been selected.
            this.changeState( 'not-searching' );

            /**
             * Tracks the index of the item currently active due to keyboard input.
             *
             * -1 means no selection.
             *
             * @type {number}
             */
            this.keyboardSelection = -1;

            this.searchResults = [];

            if ( this.model.FocusOnLoad )
            {
                this.phraseBox.focus();
            }
        }
    }

	if ( arguments.length )
	{
		// Set the default width of the search control.
		this.setWidth( 200 );
	}
};

searchControl.prototype = new window.gcd.core.mvp.viewBridgeClasses.SelectionControlViewBridge();
searchControl.prototype.constructor = searchControl;

searchControl.prototype.createDom = function()
{
    this.interfaceContainer = $( "<div class='search-control'></div>" );

    this.phraseBox = $( "<input type='text' value='' class='phrase-box'/>" );
    this.selectedLabel = $( "<span />" );
    this.clearButton = $( "<input type='button' value='Clear' />" );
    this.resultsTable = $( "<table width='100%' class='results-list'><tbody></tbody></table>");
    this.resultsList = $( "tbody", this.resultsTable );
    this.resultsContainer = $( "<div class='results drop-down' style='z-index: 1000'></div>" );
    this.buttonsContainer = $( "<div class='button-container inline'></div>" );

    this.resultsContainer.append( this.resultsTable );
    this.buttonsContainer.append( this.clearButton );

    this.resultsContainer.hide();

    this.interfaceContainer.append( this.phraseBox );
    this.interfaceContainer.append( this.selectedLabel );
    this.interfaceContainer.append( this.buttonsContainer );
    this.interfaceContainer.append( this.resultsContainer );
	this.onCreateDom();
};

searchControl.prototype.onCreateDom = function() { }

searchControl.prototype.setWidth = function( width )
{
	this.phraseBox.width( width + 20 );

	if ( this.model.ResultsWidth == "match" )
	{
		this.resultsContainer.outerWidth( this.phraseBox.outerWidth() + 10 );
	}
	else
	{
		this.resultsContainer.css( "width", this.model.ResultsWidth );
	}

	this.resultsContainer.height( width );
};

searchControl.prototype.setValue = function ( value )
{
	if ( this.viewNode && ( "value" in this.viewNode ) ) {
		this.viewNode.value = value;
	}

	if ( value == "" || value == "0" )
	{
		this.changeState( 'unselected' );
		this.phraseBox.val( '' );
	}
    else
    {
        this.selectedLabel.text( "Retrieving...");
        var self = this;

        this.raiseServerEvent( "GetItemForSingleValue", value, function( item )
        {
            self.setSelectedItems( [ item ] );
            self.valueChanged();
        });

        this.changeState( 'selected' );
    }
};

searchControl.prototype.hasKeyboardSelection = function()
{
    return ( this.keyboardSelection > -1 );
};

searchControl.prototype.attachEvents = function()
{
    var self = this;

    this.phraseBox.keypress( function( e )
    {
        if (e.keyCode == 13 )
        {
            if ( self.hasKeyboardSelection() )
            {
                self.keyboardSelect();
            }
            else
            {
                self.submitSearch();
            }

            e.preventDefault();
            return false;
        }
    } );

    this.phraseBox.keydown( function( e )
    {
        if (e.keyCode == 38 )
        {
            self.keyboardUp();
            return false;
        }

        if (e.keyCode == 40 )
        {
            self.keyboardDown();
            return false;
        }
    });


    this.phraseBox.keyup( function( e )
    {
        // We aren't interested in a range of characters that can't have any affect on search results and we
        // need to make sure they don't trigger auto search if supported below.
        if ( e.keyCode == 37 || e.keyCode == 39 || e.keyCode == 38 || e.keyCode == 40 || e.keyCode == 13 )
        {
            return true;
        }

        // If the setting to auto search is true, start a timer that will initiate the search.
        if ( self.model.AutoSubmitSearch )
        {
            if ( self.autoSearchTimer )
            {
                clearTimeout( self.autoSearchTimer );
            }

            if ( self.model.MinimumPhraseLength )
            {
                if ( self.phraseBox.val().length < self.model.MinimumPhraseLength )
                {
                    return;
                }
            }

            self.autoSearchTimer = setTimeout( function(){ self.submitSearch() }, 300 );
        }
    });


    this.clearButton.click( function()
    {
       //self.setSelectedValueAndLabel( "", "" );

       self.changeState( 'unselected' );

       self.phraseBox.focus().select();

       self.onClearPressed();
    });

    $( document).on( 'click.searchControl', function( e )
    {
        // If the user has clicked outside of the control elements we make sure the search results
        // are closed.
        if ( $( e.target).parents().filter( self.interfaceContainer).length == 0 )
        {
            self.resultsContainer.hide();
        }
    });
};

searchControl.prototype.keyboardSelect = function()
{
    // Get the item represented by index and call itemDomSelected
    this.itemDomSelected( $( this.resultsList.children()[ this.keyboardSelection ] ) );
};

searchControl.prototype.keyboardUp = function()
{
    this.keyboardSelection--;

    if ( this.keyboardSelection < -1 )
    {
        this.keyboardSelection = -1;
    }

    this.highlightKeyboardSelection();
};

searchControl.prototype.keyboardDown = function()
{
    this.keyboardSelection++;

    if ( this.keyboardSelection >= this.searchResults.length )
    {
        this.keyboardSelection = this.searchResults.length - 1;
    }

    this.highlightKeyboardSelection();
};

searchControl.prototype.highlightKeyboardSelection = function()
{
    this.resultsList.children().removeClass( 'active' );

    if ( this.keyboardSelection < 0 )
    {
        return;
    }

    $( this.resultsList.children()[ this.keyboardSelection ]).addClass( 'active' );
};

searchControl.prototype.changeState = function( newState )
{
    if ( newState == 'not-searching' )
    {
        newState = ( this.element.val() != '' && this.element.val() != '0' ) ? 'selected' : 'unselected';
    }

    this._state = newState;
    this.updateUiState();
};

searchControl.prototype.updateUiState = function()
{
    this.phraseBox.hide();
    this.clearButton.hide();
    this.selectedLabel.hide();
    this.resultsContainer.hide();
	this.phraseBox.removeClass( "phrase-box-searching" );

    switch( this._state )
    {
        case "unselected":
            this.phraseBox.show();
            break;
        case "searching":
			this.phraseBox.addClass( "phrase-box-searching" );
			this.phraseBox.show();
            this.resultsContainer.show();
            break;
        case "searched":
            this.phraseBox.show();
            this.resultsContainer.show();
            break;
        case "selected":
            this.selectedLabel.show();
            this.clearButton.show();
            break;
    }

    // If the ui state is updating then a significant update to our model has happened and we should
    // void any keyboard selection
    this.keyboardSelection = -1;
    this.highlightKeyboardSelection();
};

searchControl.prototype.onClearPressed = function()
{
};

searchControl.prototype.onCancelPressed = function()
{
};

searchControl.prototype.submitSearch = function()
{
    this.resultsList.html( '' );
    this.changeState( 'searching' );

    var phrase = this.phraseBox.val();
    this.beforeSearchSubmitted( phrase );

    var self = this;

    this.raiseServerEvent( 'SearchPressed', phrase, function( result )
    {
        self.changeState( 'searched' );
        self.onSearchResultsReceived( result );
    });
};

searchControl.prototype.beforeSearchSubmitted = function( phrase )
{

};

searchControl.prototype.onSearchResultsReceived = function( items )
{
    this.keyboardSelection = -1;
    this.highlightKeyboardSelection();

    this.searchResults = items;
    this.resultsList.html( '' );

    for( var i in items )
    {
        var item = items[i];
        var itemDom = this.createResultItemDom( item );

        this.resultsList.append( itemDom );
    }
};

searchControl.prototype.getItemLabel = function( itemData )
{
    return itemData[1];
};

searchControl.prototype.createItemLabelDom = function( labelString )
{
    return labelString;
};

searchControl.prototype.itemDomSelected = function( itemDom )
{
    var item = itemDom.data( 'item' );
    this.setSelectedItems( [ item ] );
	this.valueChanged();
};

searchControl.prototype.setSelectedItems = function( items, raiseServerEvent )
{
    window.gcd.core.mvp.viewBridgeClasses.SelectionControlViewBridge.prototype.setSelectedItems.apply( this, arguments );

	for( var value in items )
	{
        var item = items[ value ];
        var labelDom = this.createItemLabelDom( item.label );
        var self = this;

        this.selectedLabel.html( labelDom );

        this.setInternalValue( item.value );

        if ( raiseServerEvent )
        {
            this.raiseServerEvent( 'ItemSelected', item, function( result )
            {
                self.itemSelected( result );
            });
        }

		break;
    }
};

searchControl.prototype.itemSelected = function( result )
{

};

searchControl.prototype.setInternalValue = function( value )
{
    this.element.find( 'input[name="' + this.presenterPath + '"]').val( value );
    this.changeState( 'selected' );
};

searchControl.prototype.createResultItemDom = function( item )
{
    var itemDom = $( '<tr class="-item"></tr>' );

	for( var i = 0; i < this.model.ResultColumns.length; i++ )
	{
		var column = this.model.ResultColumns[i];

		if ( typeof item.data[ column ] != 'undefined' )
		{
			itemDom.append( '<td>' + item.data[ column ] + '</td>' );
		}
		else
		{
			itemDom.append( '<td></td>' );
		}
	}

    itemDom.data( 'value', item.value );
    itemDom.data( 'item', item );

    var self = this;

    // This would be more efficient as an event on the outer list, however that would mean knowing the correct
    // child selector which might change and also fragments the code a little.
    itemDom.on( 'click', function()
    {
        self.itemDomSelected( $( this  ) );
    });

    return itemDom;
};

window.gcd.core.mvp.viewBridgeClasses.SearchControl = searchControl;
