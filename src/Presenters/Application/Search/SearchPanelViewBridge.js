var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.onSubPresenterValueChanged = function () {
    if (this.model.AutoSubmit) {
        this.raiseServerEvent("Search");
    }
};

window.rhubarb.viewBridgeClasses.SearchPanelViewBridge = bridge;