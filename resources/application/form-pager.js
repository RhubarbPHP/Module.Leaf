var formPager = function (leafPath) {
    window.rhubarb.viewBridgeClasses.JqueryViewBridge.apply(this, arguments);
};

formPager.prototype = new window.rhubarb.viewBridgeClasses.JqueryViewBridge();
formPager.prototype.constructor = formPager;

formPager.prototype.attachEvents = function () {
    var self = this;

    this.element.find(".pages a").click(function () {
        self.element.find(".page-input").val($(this).data('page'));
        self.element.parents('form')[0].submit();

        return false;
    });
};

window.rhubarb.viewBridgeClasses.FormPager = formPager;