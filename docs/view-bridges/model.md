The Model
=========

Every Leaf's model is propagated into the HTML by JSON encoding it and storing it in a hidden input.
Not all model properties should be propagated in this way however. Some may be extremely large and
complex objects (although that is usually not in the spirit of Leaf/Model/View). Some may be 
sensitive (internal IDs or hashes) and not to be disclosed.

Leaf takes a secure approach - only properties registered as 'public exposable' are encoded and
propagated through hidden HTML state.

To add properties to this list, simply implement the `getExposableModelProperties` method on your
Leaf Model:

```php
class MyLeafModel
{
    public $exposedValue = "insecure thing";
    
    public $secretValue = "secure thing";
    
    protected function getExposableModelProperties()
    {
        $properties = parent::getExposableModelProperties();
        $properties[] = 'exposedValue';
        return $properties;
    }
}
```

The property name should match the public field on the class.

> Don't forget to call the parent implementation. Two key properties are added to the list there
> namely the leafName and leafPath

## Using the model

In addition to propagation, the model is available to your View Bridge and so can be a means
to pass settings and data to the Javascript environment.

```js
console.log(this.model.exposedValue);
```

If you have made changes to the model in Javascript and want to see these propagated you must
call `saveState` to re-encode the model into the hidden HTML input:

```js
this.model.exposedValue = 'new value';
// Ensure the 'hidden' frozen HTML state is updated
this.saveState();
``` 