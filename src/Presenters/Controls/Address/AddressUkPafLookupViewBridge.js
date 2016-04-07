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
        searchResultsMsg = $(".search-results-msg"),
        resultItemsList = $(".search-results-items"),
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
        resultItemsList.empty();
        manualAddressPar.show();
        manualAddressElements.hide();
        searchAddressElement.show();
        searchLink.hide();
    });
    // search address
    searchButton.attachClientEventHandler("OnButtonPressed", function() {
        searchResultsMsg.removeClass(alertClass).empty();
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
                if(response.length > 0) {
                    searchResultsMsg.addClass(alertClass).append("We found " + response.length + " results");
                    var resultString = "";
                    for(var i in response) {
                        var currItem = response[i];
                        resultString +=
                            "<li class='result-item'>"
                            + "<span class='AddressLine1'>" + currItem['AddressLine1'] + "</span>"
                            + "<span class='AddressLine2'>" + currItem['AddressLine2'] + "</span>"
                            + "<span class='Town'>" + currItem['Town'] + "</span>"
                            + "<span class='County'>" + currItem['County'] + "</span>"
                            + "<span class='Postcode'>" + currItem['Postcode'] + "</span>"
                            + "</li>";
                    }
                    resultItemsList.html(resultString);
                } else {
                    searchResultsMsg.addClass(alertClass).append("The search didn't give any result, try with another post code or enter the address manually");
                }
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
    function setAddressFields(addressObj) {
        line1.viewNode.value = addressObj['AddressLine1'];
        line2.viewNode.value = addressObj['AddressLine2'];
        town.viewNode.value = addressObj['Town'];
        county.viewNode.value = addressObj['County'];
        postCode.viewNode.value = addressObj['Postcode'];
    }
    // click event on resultItem of the search, map values in array and set address fields
    resultItemsList.on("click", "li.result-item", function() {
        var itemValues = $(this).find("span"),
            addressObj = {};

        itemValues.each(function() {
            var currEl = $(this);
            addressObj[ currEl.attr('class') ] = currEl.text();
        });
        setAddressFields(addressObj);
        showAddressFields();
    });
};

window.rhubarb.viewBridgeClasses.AddressUkPafLookupViewBridge = bridge;