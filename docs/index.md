Introduction to Leaf
====================

Leaf is a rapid user interface building platform for PHP which allows for a hierarchical componentised approach.
It has been designed with testing and extensibility as a primary goal.

## Why 'Leaf'

The leaf is the visible surface of the Rhubarb plant so it seemed to be a fitting name. Beyond the
Rhubarb plant it also reflects something of its nested nature when thinking of a tree of leaves. Ultimately
it's just a name which helps us distinguish between classes when talking about them.

## The Leaf Pattern

Leaf borrows something from both the
[Model-View-Presenter](http://martinfowler.com/eaaDev/uiArchs.html#Model-view-presentermvp) pattern and the
[Presenter-Model](http://martinfowler.com/eaaDev/PresentationModel.html) pattern.

There are three classes for every UI component:

View
:   A presentation only class responsible for translating the state held by the `LeafModel` into HTML.

LeafModel
:   Represents the public state of the component and should contain everything needed to build or test the interface.
    Not to be confused with domain models.

Leaf
:   Performs all user interface decision making logic and can call methods on domain models to effect lasting changes.

These classes allow us to form some simple rules which allow for excellent abstraction, code clarity and testability:

1. Only put decision making logic in the Leaf class. The decision could be as small as when to show a feedback
message, but if it's not decided in the Leaf class it can't be tested.
2. Only interact with the domain model from the Leaf class, never the View. A View can be given domain models to
use for presentation, but it should never modify their state or call functions with side effects.
3. The Leaf should not modify the state of the View directly, it should modify the LeafModel class.
4. Most important of all, the View should **never** know about or depend upon its Leaf class, only the LeafModel class.
It can however raise Events to inform the Leaf class of a user interaction.

Let's consider these classes in more detail.

### The 'LeafModel' class

In some ways this is the most important class of the three. The LeafModel defines the contract between which
a Leaf class and the View class can interact.

While a Leaf will have a default view class, it is possible to switch it to any other view as long as that view
class expects the same type of LeafModel. Similarly a view can be used with multiple Leaves if they all share the
same Leaf Model.

A LeafModel primarily defines:

1. Public properties to store the state of the component.
2. Public event object properties which define what notices the View can give to the Leaf.

In addition a LeafModel might also have:

3. A list of properties that can be serialised and propagated in the HTML form (and given to client side
ViewBridge javascript classes)
4. Default values
5. Convenience methods to make settings or getting properties simpler.

``` php file[examples/HelloWorld/HelloWorldModel.php] lines[6] demo[examples/HelloWorld/HelloWorld]
```

### The 'View' class

The view class transforms the LeafModel and its properties into HTML output. The most important function to
override is `printViewContent()`

``` php file[examples/HelloWorld/HelloWorldView.php] lines[6] demo[examples/HelloWorld/HelloWorld]
```

In this example we're defining the model as a type of `ProfileModel` and accessing its properties during
presentation.

> Note that while the main Leaf module doesn't support templating formats and libraries like smarty there is
> no reason why a template based view couldn't be created. We generally find that when the state is contained
> in a single class PHP is an excellent HTML generation tool.

View classes can create any number of sub leaf objects to help it construct it's interface. Common examples would
be text boxes, buttons, drop downs etc.

### The 'Leaf' class

The Leaf class should be the only class making behavioural decisions in response to user interactions. Usually
it makes these decisions when responding to an event raised by the View.

In addition to responding to user interactions the Leaf class must also declare what View class should be used
with the Leaf by default and must instantiate its LeafModel class.

``` php file[examples/HelloWorld/HelloWorld.php] lines[6] demo[examples/HelloWorld/HelloWorld]
```