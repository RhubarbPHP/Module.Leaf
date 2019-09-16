var eventPager = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.UrlStateViewBridge.apply(this, arguments);
};

eventPager.prototype = new window.rhubarb.viewBridgeClasses.UrlStateViewBridge();
eventPager.prototype.constructor = eventPager;

eventPager.prototype.attachEvents = function () {
    var self = this;

    var aTags = this.viewNode.querySelectorAll(".pages a");

    for(var i = 0; i < aTags.length; i++){
        var aTag = aTags[i];
        aTag.addEventListener('click',function(event) {
            var page = event.target.getAttribute('data-page');

            // If our presenters are configured for it we also notify the
            // server side with an event.

            self.setUrlStateParam(page);
            self.raiseServerEvent("PageChanged", page);
            event.preventDefault();
            return false;
        });
    }
};

window.rhubarb.viewBridgeClasses.EventPagerViewBridge = eventPager;
