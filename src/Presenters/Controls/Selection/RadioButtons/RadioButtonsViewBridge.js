var radioButtonsViewBridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.SelectionControlViewBridge.apply(this, arguments);

    this.supportsMultipleSelection = false;
};

radioButtonsViewBridge.prototype = new window.rhubarb.viewBridgeClasses.SelectionControlViewBridge();
radioButtonsViewBridge.prototype.constructor = radioButtonsViewBridge;

radioButtonsViewBridge.prototype.setValue = function (value) {
    this.element.find('input[type=radio][value=' + value + ']').prop('checked', true);

    this.model.SelectedItems = [{"value": value}];
};

window.rhubarb.viewBridgeClasses.RadioButtonsViewBridge = radioButtonsViewBridge;
