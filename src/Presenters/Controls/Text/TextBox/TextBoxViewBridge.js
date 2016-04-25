var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.ViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.ViewBridge();
bridge.prototype.constructor = bridge;

bridge.spawn = function (spawnData, index, parentpresenterPath) {
    var textBox = document.createElement("INPUT");
    textBox.setAttribute("type", spawnData.type);
    textBox.setAttribute("size", spawnData.size);
    if (spawnData.maxLength) {
        textBox.setAttribute("maxlength", spawnData.maxLength);
    }

    window.rhubarb.viewBridgeClasses.ViewBridge.applyStandardAttributesToSpawnedElement(textBox, spawnData, index, parentpresenterPath);

    return textBox;
};

bridge.prototype.keyPressed = function(event){
    if (this.onKeyPress){
        this.onKeyPress(event);
    }
};

bridge.prototype.attachDomChangeEventHandler = function (triggerChangeEvent) {
    window.rhubarb.viewBridgeClasses.ViewBridge.prototype.attachDomChangeEventHandler.apply(this,arguments);

    var self = this;

    if (!this.viewNode.addEventListener) {
        this.viewNode.attachEvent("onkeypress", self.keyPressed.bind(self));
    }
    else {
        // Be interested in a changed event if there is one.
        this.viewNode.addEventListener('keypress', self.keyPressed.bind(self), false);
    }
};

bridge.prototype.getCssDisplayType = function () {
    return 'inline-block';
};

window.rhubarb.viewBridgeClasses.TextBoxViewBridge = bridge;
