var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.ViewBridge.apply(this, arguments);

    this.searchTimer = null;
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.ViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onRegistered = function() {
    window.rhubarb.viewBridgeClasses.ViewBridge.prototype.onRegistered.apply(this,arguments);

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
};

/**
 * A place to update the interface to signal the start of a search
 */
bridge.prototype.onSearchStarted = function(){

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