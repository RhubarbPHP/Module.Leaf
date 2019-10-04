var bridge = function (leafPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.spawn = function (spawnData, index, parentPresenterPath) {
    var hidden = document.createElement("INPUT");
    hidden.setAttribute("type", "hidden");

    window.rhubarb.viewBridgeClasses.HtmlViewBridge.applyStandardAttributesToSpawnedElement(hidden, spawnData, index, parentPresenterPath);

    return hidden;
};

window.rhubarb.viewBridgeClasses.HiddenViewBridge = bridge;
