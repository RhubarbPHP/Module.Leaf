var dropDown = function (presenterPath) {
    window.rhubarb.viewBridgeClasses.SelectionControlViewBridge.apply(this, arguments);

    if (!presenterPath) {
        return;
    }

    // As this view bridge doesn't carry a hidden state we need to build
    // the SelectedItems. Note the item jquery data entry exists due to
    // presence of the data-item attribute on the option tag.
    var selectedItems = [];

    this.element.find("option").each(function () {
        if (!$(this).data("item")) {
            $(this).data("item", {value: this.value, label: this.text});
        }

        if (this.selected) {
            selectedItems.push($(this).data('item'));
        }
    });

    this.model.SelectedItems = selectedItems;

    // hasAttribute would be better - but this isn't IE 7 compatible
    this.supportsMultipleSelection = ( this.element[0].getAttribute("multiple") != null );
};

dropDown.prototype = new window.rhubarb.viewBridgeClasses.SelectionControlViewBridge();
dropDown.prototype.constructor = dropDown;

dropDown.spawn = function (spawnSettings, viewIndex, parentPresenterPath) {
    var element = document.createElement("SELECT");

    window.rhubarb.viewBridgeClasses.HtmlViewBridge.applyStandardAttributesToSpawnedElement(element, spawnSettings, viewIndex, parentPresenterPath);

    for (var i in spawnSettings.AvailableItems) {
        var item = spawnSettings.AvailableItems[i];

        var option = document.createElement("OPTION");
        option.textContent = item.label;
        option.value = item.value;

        element.appendChild(option);
    }

    return element;
};

dropDown.prototype.valueChanged = function () {
    var selectedItems = [];

    this.element.find("option").each(function () {
        if (this.selected) {
            selectedItems.push($(this).data('item'));
        }
    });

    this.setSelectedItems(selectedItems);

    // Calling our parent will ensure the new value gets raised as an event
    window.rhubarb.viewBridgeClasses.SelectionControlViewBridge.prototype.valueChanged.apply(this, arguments);
};

dropDown.prototype.getDisplayView = function () {
    return $("option:selected", this.element).text();
};

dropDown.prototype.setCurrentlyAvailableSelectionItems = function (items) {
    var oldValue = this.element.val();
    this.element.html('');

    for (var i in items) {
        var item = items[i];
        var itemDom = $('<option value="' + item.value + '">' + item.label + '</option>');

        itemDom.data("item", item);

        this.element.append(itemDom);
    }

    this.element.val(oldValue);
};

window.rhubarb.viewBridgeClasses.DropDownViewBridge = dropDown;
