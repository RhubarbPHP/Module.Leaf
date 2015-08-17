var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.attachEvents = function(){

    var manualAddressElements = $(".manual-fields"),
        searchAddressElement = $(".search-fields"),
        insertManualAddressLink = $(".manual-address-link"),
        manualAddressPar = $(".manual-address-par"),
        searchLink = $(".search-address-link");

    manualAddressElements.hide();
    searchLink.hide();

    insertManualAddressLink.click(function() {
        manualAddressPar.hide();
        manualAddressElements.show();
        searchAddressElement.hide();
        searchLink.show();
    });

    searchLink.click(function() {
        manualAddressPar.show();
        manualAddressElements.hide();
        searchAddressElement.show();
        searchLink.hide();
    });

};

window.rhubarb.viewBridgeClasses.AddressViewBridge = bridge;