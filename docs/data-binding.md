Data Binding
============

[Control components](controls/index) support data binding and get their value directly from the LeafModel of the hosting
View. Conversely when the value changes the control will automatically change the value in the LeafModel. Data binding
greatly reduces the amount of plumbing needed to build user interfaces.

## BindableLeafInterface

Bindable components are Leaf classes that implement `BindableLeafInterface`. They must implement the following
functions:

getValue()
:   The component should return the current value it represents.

getBindingValueChangedEvent()
:   The component should return a reference to its event object which will be listened to by the host
    and triggered by the component when the component value changes.

getBindingValueRequestedEvent()
:   The component should return a reference to its event object which will be listened to by the host
    and triggered by the component when it needs to request its value from the host.

[Control components](controls/index) wrap this plumbing in a handy pattern.

## Using Data Binding

Data Binding is supported natively by all View classes, you simply need to create and host a bindable component
on it which normally that means using simple control components.

The one stipulation is that the LeafModel for your View needs to declare public properties the bindable components
can bind to. The names of these properties must match the names of the bindable components exactly.

``` php
class SignUpLeafModel extends LeafModel
{
    // Binding source for the forename component
    public $forename = "";

    // Binding source for the surname component
    public $surname = "";
}

class SignUpView extends View
{
    /**
     * @var SignUpLeafModel
     */
    protected $model;

    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            new TextBox("forename"),
            new TextBox("surname")
        );
    }

    protected function printViewContent()
    {
        print $this->leaves["forename"]." ".$this->leaves["surname"];
    }
}
```