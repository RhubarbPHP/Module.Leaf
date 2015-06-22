var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.spawn = function (spawnData, index) {
    var textBox = document.createElement("INPUT");
    textBox.setAttribute("type", "text");

    window.rhubarb.viewBridgeClasses.HtmlViewBridge.applyStandardAttributesToSpawnedElement(textBox, spawnData, index);

    return textBox;
};

window.rhubarb.viewBridgeClasses.TextBoxViewBridge = bridge;

