Events
======

There are a number of View Bridge methods for sharing information between client and server.

### raiseServerEvent

Already discussed in the introduction, `raiseServerEvent` is the most commonly used method
for communicating with the server. Call it raises an asynchronous XHR request and passes
all the arguments supplied to a corresponding event defined in the leaf's LeafModel class.

The return value from the function is encoded transparently and passed to a success
callback if one was supplied:

```js
this.raiseServerEvent(
    'doSomething',  // The name of the event
    argument1,      // Any number of arguments
    argument2,
    function(response){ // A success callback
        
    },
    function(){         // A failure callback
        
    });
```

Note that the corresponding event in the LeafModel should be suffixed with 'Event' - in
this case it should be called `doSomethingEvent`.

`raiseServerEvent` returns a reference to the XMLHttpRequest object being used to
transmit the event to allow it to be cancelled when appropriate.

### raisePostBackEvent

Raises an event on the server by posting the page in much the same way as a normal
button control would. This is sometimes used when a navigation like feel is actually
preferred over a transparent XHR request.

### sendFileAsServerEvent

Pushes a file from the client to the server. Often used by HTML 5 upload components.

```js
var xmlhttp = this.sendFileAsServerEvent(
    'avatarUploaded',
    file,
    function(event) {
        // Called as progress is reported.
        event.loaded;
        event.total;
    },
    function(){
        // Called on completion
    },
    function(){
        // Called on failure
    });
```

The XMLHttpRequest object is returned and should be used to cancel the upload.

The receiving PHP event handler will be passed an UploadedFileDetails object as an
argument once the upload completes.

### raiseClientEvent

This type of event does not reach the server but allows a View Bridge to communicate with it's
host or parent in a loosely coupled way. Controls for example will raise a client event when
their value changes so that a hosting form can be notified of changes without having to 
know what type of control any particular control is.

```js
var response = this.raiseClientEvent(
    'ValueChanged',         // The event name - an arbitrary string
    argument1,              // Any number of arguments
    argument2
)
```

The event is handled by attaching a client event handler:

```js
this.findChildViewBridge('Name').attachClientEventHandler('ValueChanged', function(argument1, argument2){
    return 'a response';
})
```

Note that if there are multiple handlers, the response received by raiseClientEvent() is the last response
returned. If a handler does not need to return a value it should not do so, deferring politely to others in
the chain.

## Model changes

All events that reach the server submit the hidden model state for all leaves as part of the request.
This is necessary to ensure events process in a predictable way. If as a result of event processing
the model should be changed in PHP, the new model will be transmitted back to the client side
and updated transparently.

If your View Bridge needs to know when this happens you can implement the `onModelUpdatedByEvent`
method.
