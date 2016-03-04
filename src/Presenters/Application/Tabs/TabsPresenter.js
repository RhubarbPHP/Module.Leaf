var tabsPresenter = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.JqueryHtmlViewBridge.apply(this, arguments);
};

tabsPresenter.prototype = new window.rhubarb.viewBridgeClasses.JqueryHtmlViewBridge();
tabsPresenter.prototype.constructor = tabsPresenter;

tabsPresenter.prototype.attachEvents = function () {
    var self = this;

    this.element.find('li').click(function () {
        var lis = $(this).parent()[0].childNodes;
        var index = Array.prototype.indexOf.call(lis, this);

        self.raiseServerEvent("TabSelected", index);

        $('ul:first', self.element).children().removeClass('selected');
        $(this).addClass('selected');
    });
};

window.rhubarb.viewBridgeClasses.Tabs = tabsPresenter;