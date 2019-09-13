var table = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.UrlStateViewBridge.apply(this, arguments);
};

table.prototype = new window.rhubarb.viewBridgeClasses.UrlStateViewBridge();
table.prototype.constructor = table;

table.prototype.attachEvents = function () {
    var self = this, $ = jQuery;

    this.element.find('thead th.sortable').click(function () {
        var index = $.inArray(this, $('thead th', self.viewNode));

        self.raiseServerEvent('ColumnClicked', index);
        
        if (self.model.urlStateName) {
            // Force string comparison to ensure -0 is seen as different from 0
            if (self.getUrlStateParam() === '' + index) {
                self.setUrlStateParam('-' + index);
            } else {
                self.setUrlStateParam(index);
            }
        }
    });

    this.element.find('tbody tr td.clickable').click(function () {
        var tr = $(this).parents('tr:first');
        self.raiseClientEvent('RowClicked', tr);
    });
};

window.rhubarb.viewBridgeClasses.TableViewBridge = table;
