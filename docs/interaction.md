Processing User Interactions
============================

The View class is responsible for detecting user interactions and
raising named events so the Leaf class can process them, make
decisions, update the LeafModel, and ultimately refresh the View.

## Detecting interactions directly using the `WebRequest` object

As the View class generates the HTML output it 'knows'
about how a browser behaves and so you have freedom to decide how
to detect user interaction. View classes can define the 'parseRequest()'
function which will be called by the Leaf system and provides your
View with an opportunity to detect interactions:

``` php
class BlogArticleView extends View
{
    public function parseRequest(WebRequest $request)
    {
        // Detect if the request contains ?helpful=1
        if ($request->get("helpful", false)){
            // Raise an event to the Leaf so it can update the blog
            // article 'helpful' score
            $this->model->voteHelpfulEvent->raise();
        }
    }
    
    protected function printViewContent()
    {
        ?>
        <!-- Lots of lovely blog related HTML here.... -->
        <p><a href='?helpful=1'>I found this helpful.</a></p>
        <?php
    }
}
```

> Many control components will use this approach to detect changes to
> their values.

## Detecting interactions using nested components

Detecting interactions manually involves repeated plumbing code
and can introduce fragility. The `module-leaf-common-controls` module
provides that wrap the standard HTML form inputs into a collection of
ready made control components. These components handle the manual
detection of interaction for you and raise simple events you can
handle.

Because data binding handles events for you from input controls the
most common component you need to actually configure for interaction
is the Button component. Here is the same example rewritten using
a Button component.

``` php
class BlogArticleView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            new Button("VoteHelpful", "I found this helpful", function(){
                $this->model->voteHelpfulEvent->raise();
            });
    }
    
    protected function printViewContent()
    {
        ?>
        <!-- Lots of lovely blog related HTML here.... -->
        <p><?=$this->leaves["VoteHelpful"];?></p>
        <?php
    }
}
```

> This approach is the favoured approach if it is possible to build
> your interface using nested leaf components.

