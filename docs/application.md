Application Components
======================

An Application Leaf is a building block for building common user interface elements in a 
web based application.

They are designed to inter-operate with each other in a loosely coupled way, augmenting each other
by detecting and handling events as appropriate.

For example a `Table` component will display a paged list of records, but by connecting it with
a `SearchPanel` component that list can now be filtered by user inputs. Connect the `SearchPanel`
component to a `SearchPanelTabs` component and now the search values can be changed by selecting
tabs.

## Binding events

Application components are bound together in the hosting View by calling `bindEventsWith()` on
one component while passing the other. `bindEventsWith()` is symmetrical in that it doesn't matter
which way around you call it: `$a->bindEventsWith($b)` is the same as `$b->bindEventsWith($a)`.

Internally `bindEventsWith()` calls a protected method `bindEvents()` on each Leaf, passing the other
as the argument.

`bindEvents()` will then discover if they can interoperate with the Leaf being bound and if so 
attach event handlers to do so.

> Note that bindEvents() should not use `instanceof` to determine the type of object being passed.
> Each application component exists in it's own composer project and can't assume the project
> has access to those other classes. Instead look for the signature of certain public properties
> or methods existing - usually public event variables.