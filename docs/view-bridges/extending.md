Extending View Bridges
======================

When extending a View class it is often (not always) necessary to extend the View Bridge as well
to provide client side support for the new features being added.

```js
rhubarb.vb.create('MyExtendedViewBridge', function(parent){
    return {
        
    };
}, rhubarb.viewBridgeClasses.MyBaseViewBridge);
```

The key difference from creating a new View Bridge is that we pass a third argument to `create`
which is a reference to the base View Bridge class previously registered (registered
View Bridges are added to the rhubarb.viewBridgeClasses object). That class is
passed to our creation function as a handy variable `parent`. This allows us to call the parent
implementation of functions when we 'override' them:

```js
rhubarb.vb.create('MyExtendedViewBridge', function(parent){
    return {
        onReady(){
            // Some extended type behaviour would go here
            
            // Now call the base implementation:
            parent.onReady.call(this); 
        }  
    };
}, rhubarb.viewBridgeClasses.MyBaseViewBridge);
```

Calling the parent is often extremely important but all too easily forgotten.

## Installing the extended View Bridge

In your View class you need to use the name of your extended view and carefully construct a
deployment package to contain both View Bridge files *in the correct order*:

```php
class MyExtendedView extends MyBaseView
{
    public function getDeploymentPackage()
    {
        return new LeafDeploymentPackage(
            __DIR__ . '/MyBaseViewBridge.js',
            __DIR__ . '/MyExtendedViewBridge.js',
            );
    }

    protected function getViewBridgeName()
    {
        return 'HomepageViewBridge';
    }
}
```

To ensure the View Bridges are loaded in the correct order it's more usual to call the
parent View's `getDeploymentPackage()` method and add your extended View Bridge file to
the list:

```php
class MyExtendedView extends MyBaseView
{
    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . '/MyExtendedViewBridge.js';
        
        return $package;
    }
}
```