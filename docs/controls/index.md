Controls
========

## What is a control?

A control is a Leaf component that reflects a stored value into an interface users can use to change that value.

There are two golden rules to remember about controls:

1. Controls **should only ever change their value**. They **should not perform interactions** of their own with your
data model or have any other side effects.
2. A control must be completely independent and therefore **completely reusable**.

Controls are a great pattern because they enshrine the single responsability principle. It has just one job: to
to allow a value to modified by the user. What happens to the value once it has changed is a matter for higher
up the chain.

Even if a control is only used in one screen of one application you should avoid the temptation to make it violate
these two laws. If you think you really need to, it's more likely something else is wrong in your design.

## Control Leaf

While technically any leaf the observes the golden rules and is [bindable](../data-binding) can be called a control,
the pattern is encapsulted in the ready made `Control` class. All the controls found in Rhubarb's main modules and
scaffolds extend `Control` or a descendant and we would encouraged you to do likewise.

In addition to providing data binding `Control` adds some commonly needed features of controls:

setLabel($labelText)
:   Sets a piece of label text useful when the control is used with smart layout systems.

setPlaceholder($text)
:   Sets the placeholder text to be displayed when the control value is empty if supported by the control

addCssClassNames(...$className)
:   Adds one or more CSS class to the control's main HTML element.

addHtmlAttribute($attributeName, $attributeValue)
:   Adds a custom HTML attribute to the control's main HTML element.

## View bridges and controls

The [view bridge](../view-bridges/) for every control should have the following Javascript methods:

getValue()
:   Returns a Javascript representation of the value

setValue(value)
:   Populates the control interface with a Javascript value

"ValueChanged" [event]
:   Raised when the control value changes

``` javascript
// From within the hosting view bridge we can find the control:
var forename = this.findViewBridge('Forename');
// Set a value
forename.setValue('Richard');
// Retrieve the value
var value = forename.getValue();
// Register a handler for the ValueChanged event:
forename.attachClientEventHandler("ValueChanged", function(viewBridge, newValue){
    alert("The new value is: " + newValue);
});
```

## Common controls

Some controls are so ubiquitous we've compiled a range of favourites into a separate module called
["rhubarbphp/module-leaf-common-controls"](http://github.com/rhubarbphp/module.leaf.commoncontrols). If you
can't find the classes mentioned in this documentation it's likely you're missing this dependency which you
should add to your `composer.json`.