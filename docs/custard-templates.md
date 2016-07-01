Custard Templates
=================

Because each leaf set contains at least three classes (four if using a view bridge) it can introduce some overhead
each time you need to make a new interface element.

To make this process faster Leaf comes with a Custard command that can auto generate the files for you.

## Prerequisites

You must have included the Leaf module as a dependency in [`getModules()`](/manual/rhubarb/modules#content) of your
Application class. This is often overlooked and Rhubarb needs to have Leaf module registered.

You must also know the path to the custard binary. This can usually be found at vendor/bin/custard however your
composer setup may have moved this. Check your composer.json and look for "bin-dir".

## Creating a Leaf class set

Simply enter your terminal and navigate to the folder you want to create the Leaf set in.

``` bash
cd src/Leaves/Blog
```

Then invoke the leaf:create-leaf custard command:

``` bash
../../../vendor/bin/custard leaf:create-leaf
```

And follow the prompts to name the leaf.

