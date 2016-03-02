var datePicker = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.JqueryHtmlViewBridge.apply(this, arguments);
};

datePicker.prototype = new window.rhubarb.viewBridgeClasses.JqueryHtmlViewBridge();
datePicker.prototype.constructor = datePicker;

datePicker.prototype.attachEvents = function () {
    var self = this;

    this.element.datepicker(
        {
            dateFormat: 'dd/mm/yy',
            onSelect: function () {
                self.valueChanged();
            }
        }
    );
};

datePicker.prototype.getDate = function () {
    var date = this.element.datepicker('getDate');

    if (!date instanceof Date) {
        var d = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        d.setTime(d.getTime() + (-date.getTimezoneOffset() * 60 * 1000));
        date = d;
    }

    return date;
};

datePicker.prototype.setDate = function (date) {

    var jsDate = new Date(Date.parse(date));

    this.element.datepicker('setDate', jsDate);

    this.valueChanged();
};

datePicker.prototype.setValue = function(date) {
    this.setDate(date);
};

datePicker.prototype.getValue = function() {
    return this.getDate();
};

window.rhubarb.viewBridgeClasses.DatePicker = datePicker;