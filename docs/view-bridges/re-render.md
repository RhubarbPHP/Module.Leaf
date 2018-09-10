Re-Rendering
============

In most cases the correct approach to update the UI from the client side with server side
data is to raise events, receive response data and then parse the data and perform
DOM manipulation. This is a very fast and efficient process as only a small amount of
JSON formatted data is being pushed over the XHR connection.

However in some cases this approach would lead to complete duplication of HTML creation
code in both server side PHP and client side Javascript. A pragmatic option is to
force a 're-render' of the View with the updated HTML being pushed from server
to client. It is slightly slower as a lot of redundant HTML characters are being 
transmitted.

For example in the Basket of a shopping site there might be considerable work in 
presenting the HTML. Updating the basket quantities may require many different
items to be updated in ways that would need codified in the Javascript if it were
just to receive data back from the update request. This is a good candidate for
using the re-rendering technique

## reRender()

All leaves have a reRender() method. When called during a normal non XHR request
it has no effect at all. During an XHR request (e.g. as a consequence of
`raiseServerEvent()`) however it causes that Leaf's view to be rendered into
HTML again and the output string to be sent to the client.

```php
class BasketView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            new Button("updateBasket", function() {
                // Bubble the event up to the Leaf to do some heavy work.
                $this->updateBasketEvent->raise();
                // Now single a rerender so the basket is redrawn:
                $this->reRender();
            }, true );  // true to switch button event to XHR mode
        );
    }
}
```

## View Bridge reconnection

After a re-render the HTML of the view is replaced with the incoming HTML provided by the server.
This results in brand new DOM elements being created to replace the original ones. The View Bridge
base class handles reconnecting the View Bridge object to the new container DOM element (viewNode)
however it's important that all event wiring for the UI items in your View are reconfigured.

`attachEvents()` is ran on your View Bridge again after a re-render allowing you to reestablish
event handlers on UI elements.

> A common issue with re-render is event handlers being registered in `onReady` which is not fired
> after a re-render.

## Inappropriate Use

While it can seem like a universal and simple approach to handling XHR requests the re-rendering
of large bodies of complex HTML opens up a whole category of issues that are sometimes best
avoided. Generally it is simply lazy to re-render a whole page Leaf for example and fraught with
problems where state and event handlers may not restore themselves to exactly the same condition.

