Controls
========

Just as a control Leaf provides and can receive a 'value' this pattern is also reflected in the control's View Bridge

> All custom controls designed for wider reuse should have a View Bridge that implements the pattern
> set out here to ensure it will interact correctly with other View Bridges.

All View Bridges have `getValue` and `setValue` functions. If the `viewNode` of the View Bridge has a
value property the default behaviour will be to return or change this value respectively.

Complex controls will need to override these methods in order to return the complex value or
make the require DOM updates when a value is set.

```js
rhubarb.vb.create('MyComplexControlViewBridge', function() {
    return {
        getValue:function() {

        },
        setValue:function(value) {
            
        }
    };
})
```

## Detecting changes to a control value

All controls should notify their parent about changes to their value by raising the 
"ValueChanged" client event.

This is the correct way of detecting control value changes. Where possible you should
listen for this event rather than trying to 'reach into' the DOM of the child to 
attach your own DOM event handlers:

```js
rhubarb.vb.create('MyFormViewBridge', function() {
    return {
        attachEvents: function(){
            var theDropDown = this.findChildViewBridge('TheDropDown');
            
            // Bad - don't do this:
            theDropDown.viewNode.addEventListener('change', function(){
                
            });
            
            // Good - the right way:
            theDropDown.attachClientEventHandler('ValueChanged', function(viewBridge, newValue){
                            
            });
        }
    };
})
```

Notice that the event approach also gives you both the originating View Bridge and the new value.

This approach combined with `getValue` and `setValue` provide a consistent way to interact with
a control without depending on knowledge of what type of control it is. This allows a
DropDown to be changed to a CheckSet without any impact on the hosting page.

## Building your own control

When building your own control you should keep the following in mind:

1. Think about symmetry. Your control should receive it's value through `setValue()` and express any
   changes by returning the value via `getValue()`. Don't accept a value through `setValue()` but
   express values via server events for example
   
2. When your control value changes, call `this.valueChanged()`. That will trigger the
   `ValueChanged` event for you.
   
3. Make sure your control renders to the same state upon both `setValue()` or a server side
   render in PHP.