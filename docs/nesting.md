Nesting Leaves
==============

Just like any other complex software challenge it makes sense to break
down a complex user interface into smaller testable chunks. While it
is possible to make a single Leaf, View and LeafModel set handle a
complex interface that would reject many of the benefits the Leaf module
can bring you by nesting components.

Controls are a great example of component nesting. Each control
wraps a presentation of HTML along with the detection of the control's
value changing and surfaces those changes through events to the 
parent Leaf.

A Leaf for the page with a View and LeafModel *combined with*
standard controls is often enough compartmentalisation for many
tasks like a contact form for example. However more complex interfaces
may lend themselves to being composed of several smaller leaf
components interacting through events. For example a payment recording
interface might compose it's interface by creating and using
PaymentList, EntryForm and BatchTotal leaves.

## Creating nested leaves

The View **must** create any nested leaves it will need in the
special function `createSubLeaves()`. If created elsewhere the
nested leaf will not be included in event processing and so its
user interactions will not be regarded.

In `createSubLeaves()` the View should call `registerSubLeaf()` and
pass the list of leaf objects it needs.

``` php
class BlogArticleView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            new TextBox("CommentName"),
            new TextArea("Comments"),
            new Button("LeaveComment", "Leave a Comment", function(){
                // Raise an event...
            })
        );
    }
}
```

> It is important to note that the Leaf class has **no role in 
> creating nested leaves**. It is the View that is electing to create
> the nested leaves in order to fulfill its contract (via the LeafModel)
> with the Leaf class. The View could be swapped with another that
> used different nested leaves or none at all and the Leaf class would
> not be aware.

All Leaf objects have a name. Control leaves expect the name to be
passed to it as this is required to setup automatic data bindings.
Other leaf classes may not require a name in which case the class
name would be used instead.

## Printing nested leaves

Registered leaves are added to the `$leaves` array on the View class
using the leaf name as an index. Leaf objects implement the
[`__toString()`](http://php.net/manual/en/language.oop5.magic.php#object.tostring)
function and so can be 'printed' directly:

``` php
class BlogArticleView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            new TextBox("CommentName"),
            new TextArea("Comments"),
            new Button("LeaveComment", "Leave a Comment", function(){
                // Raise an event...
            })
        );
    }
    
    protected function printViewContent()
    {
        ?>
        <h2>Leave a Comment</h2>
        <p>
            <?=$this->leaves["CommentName"];?><br/>
            <?=$this->leaves["Comments"];?><br/>
            <?=$this->leaves["LeaveComment"];?>
        </p>
        <?php
    }
}
```

## Configuring nested leaves

Most nested leaves need some configuration or need to have their events
handled. You can simply do this in `createSubLeaves()` when creating
the nested leaf:

``` php
class BlogArticleView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            $name = new TextBox("CommentName"),
            $comments = new TextArea("Comments"),
            $button = new Button("LeaveComment", "Leave a Comment", function(){
                // Raise an event...
            })
        );
        
        // Configure our nested leaves:
        $name->addHtmlAttribute("placeholder", "Your name");
        $comments->addHtmlAttribute("placeholder", "Your comments");
        $button->addCssClassNames("c-button");
    }
}
```

