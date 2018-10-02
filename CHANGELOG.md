# Changelog

### 1.4.4

* Added:    Support for clearValue() and reset() in ViewBridge

### 1.4.3

* Changed:  After a client-server reRender, ValueChanged events in View Bridges are no longer forcibly
            fired.
* Added:    View Bridge documentation.

### 1.4.2

* Fixed:    Issue with splat operator for raisePostBackEvent if no arguments passed.

### 1.4.1

* Fixed     RegEx in Viewbridge getViewIndex

### 1.4.0

* Fixed:    client event handler scope is no longer a function

### 1.3.23

* Removed:  developerMode control of resource packages

### 1.3.22

* Added:    XSS protection methods to the View 

### 1.3.21

* Fixed:    model state passing when models contain &'s 

### 1.3.20

* Fixed:    CSRF disabling now at a static level

### 1.3.19

* Fixed:    Issue when viewNode isn't ready

### 1.3.18

* Fixed:    Fix for broken name generation of grandchildren.

### 1.3.17

* Fixed:    Fix for bindings not working for composite controls used with a view index.

### 1.3.16

* Fixed:    raisePostbackEvent now splats arguments properly

### 1.3.15

* Fixed:    CSRF protection for sendFileAsServerEvent

### 1.3.14

* Added:    CSRF protection now on a tag.

### 1.3.13

* Added:    Marking model state as CDATA to avoid issues with HTML tags in state

### 1.3.12

* Added:    Inbuilt CSRF protection provided by rhubarbphp/module-csrfprotection

### 1.3.11

* Added:    Adding a help function to disable the input

### 1.3.10

* Added:     --viewBridge option added to create-leaf custard command.

### 1.3.9

* Added:     Means to override constructor in new ViewBridge creation pattern

### 1.3.8

* Changed:   Ability to suppress the name attribute of the hidden state input

### 1.3.7

* Changed:   Ability to suppress the containing form if $suppressContainingForm = true

### 1.3.6

* Changed:   Control Views can easily override their value for string conversions

### 1.3.5

* Changed:   Restored use of RequiresViewConfiguration Exception to allow recreation of views

### 1.3.4

* Fixed:     Errors raised when sending files as events didn't get processed properly as failures

### 1.3.3

* Restored:  Model state changes were no longer being pushed to the client from the server during xhr events.

### 1.3.2

* Fixed:     Fix for adding ? to URL even if there are no params for the URL state

### 1.3.1

* Added:     Support for view reconfiguration

### 1.3.0

* Added:     URL State for leaves (used by table, pager and search panel leaf modules)
* Added:     Callable param in Leaf constructor allowing leaves to set model data from their params before the view is initialised

### 1.2.1

* Fixed:     When updating a leaf from a server side rerender, model state is now properly restored.
* Fixed:     Calling failedCallback if the response is supposed to be JSON but can't be parsed as JSON

### 1.2.0

* Added:     New ViewBridge creation pattern, window.rhubarb.vb.create()	     

### 1.1.10

* Added:     Added flag to Leaf to parse json responses as an associated array

### 1.1.9

* Added:     LeafModel can perform bindings to a specified array or object instead of $this.

### 1.1.8

* Added:     LeafModel now has methods for removing CSS class names and HTML attributes.
* Added:     Leaf has public methods for using add/remove CSS class and HTML attribute methods on model. 

### 1.1.7

* Added:      ViewBridge::submitForm added

### 1.1.6

* Fixed:      Added comment to fix error being shown inside PHPStorm

### 1.1.5

* Fixed:      Issue with IE ajax calls relating to href

### 1.1.4

* Fixed:		Event processing could run for another sibling if it's name included the other
* Fixed:		ViewBridge.sendFileAsEvent migrated from 0.9

### 1.1.3

* Added:    	onAfterRequestSet call for LeafModels to override data set by event processing

### 1.1.2

* Change:  	Updated custard to 1.0.9
* Fix:      	Restored build status updating to github from build script.

### 1.1.1

* Added:    	Documentation started
* Fix:      	composer.json issue with module.jsvalidation
* Fix:      	Fixed issue with view indexes not being passed as arguments in XHR button callbacks

### 1.1.0

* Fix:      	reattachViewBridges now supports leaves displayed with view indexes
* Added:   	CompositeControl pattern.
* Added:	validation module now required.
* Added:	Support for view bridge validation integration
* Added:	findViewBridgesWithIndex() to ViewBridge
* Added:	raiseServerEvent() now passes back the host leaf's state

### 1.0.1

* Changed:	Basic binding logic now moved to LeafModel

### 1.0.0

* Added:	Views now loaded using DI
* Added:	Leaf and View share a model class
* Added:	Overhaul of data binding
* Removed:	Jquery
* Removed:	Controls and patterns
* Added:    Changelog
* Changed:  Refactored to support stem 1.0.0
* Added:    codeception
* Changed:  Refactored to support Rhubarb 1.0.0
