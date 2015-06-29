var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.setValue = function (value) {
    var self = this;

    if (typeof value == "string" || value instanceof String) {
        var dateTime = self.parseIsoDatetime(value);
        var hours = dateTime.getHours();
        var minutes = dateTime.getMinutes();

        self.findChildViewBridge("Hours").setValue(hours.pad());
        self.findChildViewBridge("Minutes").setValue(minutes.pad());
    }
};

bridge.prototype.parseIsoDatetime = function (date) {
    var newDate = date.split(/[: T-]/).map(parseFloat);
    return new Date(newDate[0], newDate[1] - 1, newDate[2], newDate[3] || 0, newDate[4] || 0, newDate[5] || 0, 0);
};

Number.prototype.pad = function (size) {
    var value = String(this);

    if (value >= 10) {
        return value;
    }

    while (value.length < (size || 2)) {
        value = "0" + value;
    }

    return value;
};

bridge.prototype.hasValue = function () {
    return true;
};

bridge.prototype.getValue = function () {
    var hours = this.findChildViewBridge("Hours").getValue();
    var minutes = this.findChildViewBridge("Minutes").getValue();

    return new Date(2000, 1, 1, hours, minutes, 0);
};

window.rhubarb.viewBridgeClasses.TimeViewBridge = bridge;