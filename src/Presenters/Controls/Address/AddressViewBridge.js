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
        houseNumber = self.findChildViewBridge('HouseNumber'),
        postCodeSearch = self.findChildViewBridge('PostCodeSearch'),
        searchError = $(".search-error"),
        searchResults = $(".search-results"),
        searchButton = self.findChildViewBridge('Search'),
        // address fields
        line1 = self.findChildViewBridge('Line1'),
        line2 = self.findChildViewBridge('Line2'),
        town = self.findChildViewBridge('Town'),
        county = self.findChildViewBridge('County'),
        postCode = self.findChildViewBridge('PostCode');


    // default configuration
    manualAddressElements.hide();
    searchLink.hide();
    searchError.hide();

    // address manual entry
    insertManualAddressLink.click(function() {
        showAddressFields()
    });
    // search address
    searchLink.click(function() {
        manualAddressPar.show();
        manualAddressElements.hide();
        searchAddressElement.show();
        searchLink.hide();
    });
    // search address
    searchButton.attachClientEventHandler("OnButtonPressed", function() {
        searchResults.removeClass(alertClass).empty();
        // if post Code is empty show an error message
        if(! postCodeSearch.viewNode.value) {
            searchError.show();
            return false;
        }

        self.raiseServerEvent( "SearchPressed", houseNumber.viewNode.value, postCodeSearch.viewNode.value, function (response){
            // single result fill address fields and fill them
            if(response.length == 1) {
                showAddressFields();
                setAddressFields(response[0]);
            } else {
                searchResults.addClass(alertClass).append("We found " + response.length + " results");
            }

        });
        return false;
    });

    // show address fields
    function showAddressFields() {
        manualAddressPar.hide();
        manualAddressElements.show();
        searchAddressElement.hide();
        searchLink.show();
    }
    // set address fields
    function setAddressFields(addressObj)
    {
        line1.viewNode.value = addressObj['AddressLine1'];
        line2.viewNode.value = addressObj['AddressLine2'];
        town.viewNode.value = addressObj['Town'];
        county.viewNode.value = addressObj['County'];
        postCode.viewNode.value = addressObj['Postcode'];
    }
};

window.rhubarb.viewBridgeClasses.AddressViewBridge = bridge;