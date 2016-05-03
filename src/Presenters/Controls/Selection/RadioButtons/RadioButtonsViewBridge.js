var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.SelectionControlViewBridge.apply(this, arguments);

    this.supportsMultipleSelection = false;
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.SelectionControlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.attachEvents = function(){
    window.rhubarb.viewBridgeClasses.SelectionControlViewBridge.prototype.attachEvents.apply(this, arguments);

    var inputs = this.viewNode.querySelectorAll('input');
    var self = this;

    for( var i = 0; i < inputs.length; i++){
        inputs[i].addEventListener("click", function(event){
            if (event.target.checked){
                self.setValue(event.target.value);
                self.valueChanged();
            }
        });
    }
};

window.rhubarb.viewBridgeClasses.RadioButtonsViewBridge = bridge;
