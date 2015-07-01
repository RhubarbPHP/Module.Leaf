var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.spawn = function (spawnSettings, viewIndex, parentPresenterPath) {
    var element = document.createElement("INPUT");
    element.setAttribute("type", "file");

    window.rhubarb.viewBridgeClasses.HtmlViewBridge.applyStandardAttributesToSpawnedElement(element, spawnSettings, viewIndex, parentPresenterPath);

    return element;
};

window.rhubarb.viewBridgeClasses.SimpleHtmlFileUploadViewBridge = bridge;