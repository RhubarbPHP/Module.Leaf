View Bridges
============

A View Bridge is a Javascript class that represents the client side behaviours of a Leaf and
allows the client and server to be easily bridged.

It provides a solid pattern for building Javascript UIs without the need to build REST APIs or
use large Javascript frameworks such as React or Vue.js

### When to use a View Bridge

You should always use a View Bridge any time you require javascript interactions for your Leaf UI.

### When not to use a View Bridge

If you're building a largely client side application you should consider going all-in on a 
Javascript framework like React or Vue.js and tie it to a REST API, instead of using Leaf and
ViewBridges.

### Using a View Bridge with React or Vue.js

In a fix you can host a React of Vue.js app inside a simple Leaf/View/ViewBridge container which
allows you a very simple way to raise events back on the server instead of building a REST API.
This may not be the right thing to do however if you're just starting an application. If you
think you will need to build a public REST API anyway, you should just plan to use that.

## Creating a View Bridge

Create a file in the same directory as your View class. By convention we give it the same name as
the View with Bridge.js appended on the end.

The content should look like this: `HomepageViewBridge.js`

```js
rhubarb.vb.create('HomepageViewBridge', function() {
    return {
        onReady:function() {

        }
    };
})
```

This registers the creation of a new View Bridge and gives it a name of `HomepageViewBridge`. Again
the name is by convention the name of the View with Bridge appended on the end.

We also define our first method on the View Bridge. `onReady` is called once the DOM is read. Any
child View Bridges will also be ready when this is called.

In the view we now need to declare the file and name of the View Bridge to use: `HomepageView.php`

```php
class HomepageView extends View
{
    public function getDeploymentPackage()
    {
        return new LeafDeploymentPackage(__DIR__ . '/HomepageViewBridge.js');
    }

    protected function getViewBridgeName()
    {
        return 'HomepageViewBridge';
    }
}
```

By implementing `getDeploymentPackage()` we return a deployment package to ensure our View Bridge
Javascript file is deployed and loaded on the page. `getViewBridgeName()` confirms the name
of the View Bridge class registered by our Javascript file.

### The viewNode property

All View Bridge objects have a `viewNode` property which is a reference to the HTML DOM element
for our View's container. Usually this is a `<div>` that surrounds our View content. Control
leaves or leaves that only have one HTML element the viewNode may refer to that single element.
For example the TextBox has a viewNode property that points directly to the `<input type="text" />`
element expressed by the TextBox view.

This allows you to scope searches for child DOM elements or to manipulate the container itself:

```js
rhubarb.vb.create('HomepageViewBridge', function() {
    return {
        onReady:function() {
            // Hide the view when it's ready
            this.viewNode.style.display = 'none';
        }
    };
})
```

### attachEvents versus onReady

There is a second lifecycle method often used interchangeably with onReady called `attachEvents`.
The key difference between these two is that `onReady` is only called when the page loads.
`attachEvents` is also called on page load but is also called in the event of a server initiated
`reRender` of the view.

It's good practice to use onReady for initial initialisation but put all DOM event registrations
in the `attachEvents` method:

```js
rhubarb.vb.create('HomepageViewBridge', function() {
    return {
        onReady:function() {
            // Do initialisation things
            this.counter = 0;
        },
        attachEvents: function(){
            this.viewNode.querySelector('.hide-button').addEventListener('click', function(){
                this.viewNode.style.display = 'none';
            }.bind(this));
        }
    };
})
```

## Raising events to the server

A common task for a View Bridge is contacting the server either to pass it data to complete
an action, to fetch new information, or both.

Simply call `raiseServerEvent` to initiate a server call:

```js
rhubarb.vb.create('BasketViewBridge', function() {
    return {
        onReady:function() {
            // Do initialisation things
            this.counter = 0;
        },
        attachEvents: function(){
            this.viewNode.querySelector('.update-basket').addEventListener('click', function(){
                this.raiseServerEvent('updateBasket');
            }.bind(this));
        }
    };
})
```

On the PHP side you need to create a matching named event on the model for your Leaf: `BasketModel`

```php
class BasketModel extends LeafModel
{
    /** @var Event $updateBasketEvent */
    public $updateBasketEvent;

    public function __construct()
    {
        parent::__construct();

        $this->updateBasketEvent = new Event();
    }
}
```

And then attach a handler on either the Leaf or the View.

> While handlers can be attached from either the Leaf or the View it's extremely rare for the View
> to handle events. The exception is where the View needs to process and return new HTML for the
> View Bridge.

```php
class Basket extends Leaf
{
    /** @var BasketModel $model */
    protected $model;

    public function __construct()
    {
        parent::__construct();
    }

    protected function onModelCreated()
    {
        parent::onModelCreated();

        $this->model->updateBasketEvent->attachHandler(function(){
            $order = Order::singleton();
            $order->calculateTotals();
            
            return $order->totalPrice;
        });
    }
}
```

Notice that the event name on the server must end with the word `Event`.

The PHP event handler can return a value which is passed back to the Javascript caller.
Any value that can be json encoded can be returned: strings, ints, decimals, bools, arrays
and simple objects.

To receive the value passed back you need to supply a success callback function to the
`raiseServerEvent` call:

```js
rhubarb.vb.create('BasketViewBridge', function() {
    return {
        onReady:function() {
            // Do initialisation things
            this.counter = 0;
        },
        attachEvents: function(){
            this.viewNode.querySelector('.update-basket').addEventListener('click', function(){
                this.raiseServerEvent('updateBasket', function(totalPrice){
                    // totalPrice is received from the response of the server event.
                    
                });
            }.bind(this));
        }
    };
})
```

### Passing arguments

The View Bridge can pass any number of arguments to the server event. Like return values these
can be of any type that can be json encoded.

Simply pass the arguments after the event name and before the success callback:

```js
rhubarb.vb.create('BasketViewBridge', function() {
    return {
        onReady:function() {
            // Do initialisation things
            this.counter = 0;
        },
        attachEvents: function(){
            this.viewNode.querySelector('.apply-voucher').addEventListener('click', function(){
                var voucherCode = this.viewNode.querySelector('.voucher').value;
                
                this.raiseServerEvent('applyVoucherCode',  voucherCode, function(totalPrice){
                    // voucherCode is passed to the server.
                    
                });
            }.bind(this));
        }
    };
})
```

Simply define the argument on the event handler in the PHP to receive it:

```php
class Basket extends Leaf
{
    protected function onModelCreated()
    {
        parent::onModelCreated();

        $this->model->applyVoucherCodeEvent->attachHandler(function($voucherCode){
            $order = Order::singleton();
            $order->applyVoucher($voucherCode);
            
            return $order->totalPrice;
        });
    }
}
```