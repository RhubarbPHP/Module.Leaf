var radioButtonsViewBridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.SelectionControlViewBridge.apply(this, arguments);

    this.supportsMultipleSelection = false;
};

radioButtonsViewBridge.prototype = new window.rhubarb.viewBridgeClasses.SelectionControlViewBridge();
radioButtonsViewBridge.prototype.constructor = radioButtonsViewBridge;

radioButtonsViewBridge.prototype.setValue = function (value) {
    this.element.find('input[type=radio][value=' + value + ']').prop('checked', true);

    this.model.SelectedItems = [{"value": value}];

    this.valueChanged();
};

radioButtonsViewBridge.prototype.valueChanged = function () {
    var checked = this.element.find("input:checked");

    this.model.SelectedItems = [{"value": checked.length ? checked.val() : null}];

    // Calling our parent will ensure the new value gets raised as an event
    window.rhubarb.viewBridgeClasses.SelectionControlViewBridge.prototype.valueChanged.apply(this, arguments);
};

window.rhubarb.viewBridgeClasses.RadioButtonsViewBridge = radioButtonsViewBridge;
