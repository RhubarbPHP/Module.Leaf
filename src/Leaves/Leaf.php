<?php

namespace Rhubarb\Leaf\Leaves;

use Codeception\Lib\Interfaces\Web;
use Rhubarb\Crown\DependencyInjection\Container;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Response\GeneratesResponseInterface;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Exceptions\InvalidLeafModelException;
use Rhubarb\Leaf\Views\View;

abstract class Leaf implements GeneratesResponseInterface
{
    /**
     * @var View
     */
    protected $view;

    /**
     * @var LeafModel
     */
    protected $model;

    /**
     * The WebRequest that the presenter is responding to.
     *
     * @var WebRequest
     */
    private $request;

    private $runBeforeRenderCallbacks = [];

    public function __construct($name = "")
    {
        $this->model = $this->createModel();
        $this->onModelCreated();

        $this->initialiseView();

        if ($this->model === null || !($this->model instanceof LeafModel)) {
            throw new InvalidLeafModelException("The call to createModel on ".get_class($this).
                " didn't return a LeafModel class");
        }

        if ($name == ""){
            $name = StringTools::getShortClassNameFromNamespace(static::class);
        }

        $this->setName($name);
    }

    /**
     * Provides an opportunity for extending classes to modify the model in some way when they themselves are not
     * directly responsible for the model creation.
     */
    protected function onModelCreated()
    {

    }

    /**
     * Creates and attaches the view.
     */
    protected final function initialiseView()
    {
        $view = $this->createView();

        $this->view = $view;
    }

    /**
     * Gets the name of the leaf.
     *
     * @see $name
     * @return string
     */
    public final function getName()
    {
        return $this->model->leafName;
    }

    /**
     * Sets the name of the leaf.
     *
     * @param $name string The new name for the leaf
     * @param $parentPath string The leaf path for the containing leaf
     * @return string
     */
    public final function setName($name, $parentPath = "")
    {
        $this->model->leafName = $name;

        if ($parentPath != "") {
            $this->model->parentPath = $parentPath;
            $this->model->isRootLeaf = false;
        } else {
            $this->model->parentPath = "";
        }

        $this->updatePath();
    }

    public final function updatePath()
    {
        $ourPath = $this->model->leafName;

        if ($this->model->parentPath) {
            // Prepend the parent path if we have one.
            $ourPath = $this->model->parentPath . "_" . $ourPath;
        }

        if ($this->model->leafIndex !== null){
            // Append the view index if we have one.
            $ourPath .= "(".$this->model->leafIndex.")";
        }

        $this->model->leafPath = $ourPath;

        // Signal to all or any sub leaves that need to recompute their own path now.
        $this->view->leafPathChanged();
    }

    /**
     * Sets a view index for subsequent renders.
     *
     * @param $index
     */
    protected final function setIndex($index)
    {
        $this->model->leafIndex = $index;
        $this->updatePath();
    }

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected abstract function getViewClass();

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected abstract function createModel();

    private function createView()
    {
        $view = Container::instance($this->getViewClass(), $this->model);

        return $view;
    }

    /**
     * Set's the web request being used to render the tree of leaves.
     *
     * @param WebRequest $request
     */
    public final function setWebRequest(WebRequest $request)
    {
        $this->request = $request;

        if ($this->request) {
            $this->view->setWebRequest($this->request);
        }
    }

    /**
     * Prints the leaf using an index
     *
     * @param $index
     */
    public final function printWithIndex($index)
    {
        $this->setIndex($index);

        print $this->render();
    }

    protected function beforeRender()
    {

    }

    private final function render()
    {
        $this->runBeforeRenderCallbacks();
        $this->beforeRender();
        $html = $this->view->renderContent();

        return $html;
    }

    /**
     * Renders the Leaf and returns an HtmlReponse to Rhubarb
     *
     * @param null $request
     * @return HtmlResponse
     */
    public final function generateResponse($request = null)
    {
        $this->setWebRequest($request);

        $response = new HtmlResponse($this);
        $response->setContent($this->render());

        return $response;
    }

    function __toString()
    {
        return $this->render();
    }

    /**
     * Register a callback to run just before leaf rendering takes place.
     * @param callable $callback
     */
    protected final function runBeforeRender(Callable $callback)
    {
        $this->runBeforeRenderCallbacks[] = $callback;
    }

    /**
     * Run the before render callbacks.
     */
    public function runBeforeRenderCallbacks()
    {
        foreach($this->runBeforeRenderCallbacks as $callback){
            $callback();
        }

        $this->runBeforeRenderCallbacks = [];

        // Ask the view to notify sub leaves
        $this->view->runBeforeRenderCallbacks();
    }
}