var bridge = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.HtmlViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.HtmlViewBridge();
bridge.prototype.constructor = bridge;

bridge.prototype.attachEvents = function() {

    var self = this,
        alertClass = "c-alert",
        manualAddressElements = $(".manual-fields"),
        searchAddressElement = $(".search-fields"),
        insertManualAddressLink = $(".manual-address-link"),
        manualAddressPar = $(".manual-address-par"),
        searchLink = $(".search-address-link"),
        houseNumber = $("#DonationPresenter_Address_HouseNumber"),
        postCodeSearch = $("#DonationPresenter_Address_PostCodeSearch"),
        searchError = $(".search-error"),
        searchResults = $(".search-results"),
        searchButton = self.findChildViewBridge( 'Search' );

    // default configuration
    manualAddressElements.hide();
    searchLink.hide();
    searchError.hide();

    // address manual entry
    insertManualAddressLink.click(function() {
        searchResults.removeClass(alertClass).empty();
        manualAddressPar.hide();
        manualAddressElements.show();
        searchAddressElement.hide();
        searchLink.show();
    });
    // search address
    searchLink.click(function() {
        manualAddressPar.show();
        manualAddressElements.hide();
        searchAddressElement.show();
        searchLink.hide();
    });

    // search address
    searchButton.attachClientEventHandler("OnButtonPressed", function()
    {
        searchResults.removeClass(alertClass).empty();

        // if post Code is empty show an error message
        if(! postCodeSearch.val()) {
            searchError.show();
        }

        self.raiseServerEvent( "SearchPressed", houseNumber.val(), postCodeSearch.val(), function (response){
            searchResults.addClass(alertClass).append("We found " + response.length + " results");
        });
        return false;
    });

};

window.rhubarb.viewBridgeClasses.AddressViewBridge = bridge;