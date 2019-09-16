var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.UrlStateViewBridge.apply(this, arguments);

    this.searchTimer = null;
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.UrlStateViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onRegistered = function() {
    window.rhubarb.viewBridgeClasses.UrlStateViewBridge.prototype.onRegistered.apply(this,arguments);

    if ( this.model.AutoSubmit ){
        var subPresenters = this.getSubPresenters();
        var self = this;

        for( var i in subPresenters ){
            var subPresenter = subPresenters[i];

            // If the sub presenter is emitting key press events we need to know.
            subPresenter.onKeyPress = function(event){
                self.startSearch();
            }
        }
    }

    if (this.model.SearchButton) {
        var button = this.findChildViewBridge(this.model.SearchButton);
        if (button) {
            button.attachClientEventHandler('OnButtonPressed', function () {
                this.onSearchStarted.apply(this, arguments);
            }.bind(this));
            button.attachClientEventHandler('ButtonPressCompleted', function () {
                this.onSearchFinished.apply(this, arguments);
            }.bind(this));
            button.attachClientEventHandler('ButtonPressFailed', function () {
                this.onSearchFailed.apply(this, arguments);
            }.bind(this));
        }
    }
};

/**
 * A place to update the interface to signal the start of a search
 */
bridge.prototype.onSearchStarted = function(){
    var hasState = false;
    var state = {};

    for (var controlName in this.model.urlStateNames) {
        if (!this.model.urlStateNames.hasOwnProperty(controlName)) {
            continue;
        }

        var control = this.findChildViewBridge(controlName);
        if (control) {
            hasState = true;
            state[this.model.urlStateNames[controlName]] = control.getValue();
        }
    }

    if (hasState) {
        this.updateUrlState(state);
    }
};

/**
 * A place to update the interface to signal the end of a search
 */
bridge.prototype.onSearchFinished = function(){

};

bridge.prototype.startSearch = function(){
    if (this.searchTimer){
        clearTimeout(this.searchTimer);
    }

    var self = this;

    this.searchTimer = setTimeout( function(){
        self.onSearchStarted();
        self.raiseServerEvent("Search",function(){
            self.onSearchFinished();
        });
    }, 300);
};

bridge.prototype.onSubPresenterValueChanged = function () {
    if (this.model.AutoSubmit) {
        this.startSearch();
    }
};

window.rhubarb.viewBridgeClasses.SearchPanelViewBridge = bridge;