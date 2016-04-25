var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.ViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.ViewBridge();
bridge.prototype.constructor = bridge;

bridge.spawn = function (spawnSettings, viewIndex, parentpresenterPath) {
    var element = document.createElement("INPUT");
    element.setAttribute("type", "file");

    window.rhubarb.viewBridgeClasses.ViewBridge.applyStandardAttributesToSpawnedElement(element, spawnSettings, viewIndex, parentpresenterPath);

    return element;
};

window.rhubarb.viewBridgeClasses.SimpleHtmlFileUploadViewBridge = bridge;