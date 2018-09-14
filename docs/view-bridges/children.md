Children
========

Just as leaves and views are nested so too are their corresponding View Bridges. This allows 
compartmentalisation of the full UI into the Leaf/View/ViewBridge trilogy however most
View Bridges need to interact with their host and/or children.

While the DOM is fully accessible to any Javascript and Javascript objects have no means to
keep properties private, it is still considered poor practice for a parent View Bridge to
'reach inside' the DOM of a child View Bridge to manipulate it - DOM or View Bridge properties.

You should design your components with the following constraints:

1. To communicate with your parent, raise a [client side event](events).
2. To communicate with a child use the 'findChildViewBridge' method to get a reference to the
   View Bridge class.
3. Don't manipulate a child View Bridge directly. Create functions on the child View Bridge
   and call these from the parent.
   
### findChildViewBridge

```js
console.log(
    this.findChildViewBridge('Email').getValue()
);
```

The argument to `findChildViewBridge` is the name of the View Bridge and the response is the
View Bridge itself.

### getSubLeaves

```js
var viewBridges = this.getSubLeaves();
console.log(viewBridges[0].getValue());
```

This function returns an array of all child View Bridges.

### getSubLeafValues

```js
var viewBridgeValues = this.getSubLeafValues();
console.log(viewBridgeValues.Email);
```

Returns all the values of all child view bridges in an object. The value is evaluated by
calling `getValue()` on each View Bridge in turn.

### onSubLeafValueChanged

If you need to know when a child view bridge changes it's value you can override this function:

```js
return {
    onReady: function(){
    },
    onSubLeafValueChanged: function (viewBridge, newValue) {
        console.log(viewBridge.leafName);
        console.log(newValue);
    }
};
```

This is often used to raise server events as the user changes values on a form.