var searchPanel = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.JqueryViewBridge.apply(this, arguments);
};

searchPanel.prototype = new window.rhubarb.viewBridgeClasses.JqueryViewBridge();
searchPanel.prototype.constructor = searchPanel;

searchPanel.prototype.attachEvents = function () {
    var self = this;

    this.element.find("input:submit").click(function () {
        this.preventDefault = true;

        self.raiseServerEvent("Search");

        return false;
    });
};

window.rhubarb.viewBridgeClasses.SearchPanel = searchPanel;