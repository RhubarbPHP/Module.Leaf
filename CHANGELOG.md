# Changelog

### 1.1.1

* Added:    Documentation started
* Fix:      composer.json issue with module.jsvalidation
* Fix:      Fixed issue with view indexes not being passed as arguments in XHR button callbacks

### 1.1.0

* Fix:      reattachViewBridges now supports leaves displayed with view indexes
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
