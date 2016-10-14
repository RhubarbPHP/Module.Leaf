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

bridge.prototype.reset = function ()
{
    var form = document.createElement("FORM");
    var currentParent = this.viewNode.parentNode;
    currentParent.insertBefore(form, this.viewNode);
    form.appendChild(this.viewNode);
    form.reset();
    currentParent.insertBefore(this.viewNode, form);
    currentParent.removeChild(form);
};

window.rhubarb.viewBridgeClasses.SimpleHtmlFileUploadViewBridge = bridge;
