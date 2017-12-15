# Changelog

### 1.3.12

* Fixed:    Issue when viewNode isn't ready

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

* Views now loaded using DI
* Leaf and View share a model class
* Overhaul of data binding
* Removal of jquery
* Removal of controls and patterns
* Added a changelog
* Refactored to support stem 1.0.0
* Added codeception
* Refactored to support Rhubarb 1.0.0
