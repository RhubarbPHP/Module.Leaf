<?php

namespace Rhubarb\Leaf\Leaves;

use Codeception\Lib\Interfaces\Web;
use PhpParser\Error;
use Rhubarb\Crown\DependencyInjection\Container;
use Rhubarb\Crown\Events\Event;
use Rhubarb\Crown\Logging\Log;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Response\GeneratesResponseInterface;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\Response\XmlResponse;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Exceptions\InvalidLeafModelException;
use Rhubarb\Leaf\Exceptions\NoViewException;
use Rhubarb\Leaf\Views\View;
use Symfony\Component\Config\Definition\Exception\Exception;

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

    /**
     * True if during XHR processing the Leaf needs to push new HTML to the client.
     *
     * @see reRender();
     * @var bool
     */
    private $reRender = false;

    public function __construct($name = "")
    {
        $this->model = $this->createModel();
        $this->onModelCreated();

        $this->initialiseView();

        if ($this->model === null || !($this->model instanceof LeafModel)) {
            throw new InvalidLeafModelException("The call to createModel on " . get_class($this) .
                " didn't return a LeafModel class");
        }

        if ($name == "") {
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
     * Returns the model object for use in unit testing.
     *
     * DO NOT USE THIS TO SET PROPERTIES EXTERNALLY IN PRODUCTION CODE.
     *
     * @return LeafModel
     */
    public final function getModelForTesting()
    {
        return $this->model;
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

        if ($this->model->leafIndex !== null) {
            // Append the view index if we have one.
            $ourPath .= "(" . $this->model->leafIndex . ")";
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
        try {
            $view = Container::instance($this->getViewClass(), $this->model);
        } catch (\ReflectionException $er ){
            throw new NoViewException("The Leaf ".get_class($this)." is not configured to use a valid View class. Check `getViewClass`");
        }

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

        $this->parseRequest($request);
        $this->model->onAfterRequestSet();
    }

    /**
     * Parses the request looking for client side events.
     *
     * @param WebRequest $request
     */
    protected function parseRequest(WebRequest $request)
    {
        if ($this->model->isRootLeaf){
            $eventState = $request->post("_leafEventState");

            if ($eventState !== null) {
                $eventState = json_decode($eventState, true);

                if ($eventState) {
                    $this->model->restoreFromState($eventState);
                }
            }
        }

        $targetWithoutIndexes = preg_replace('/\([^)]+\)/', "", $request->post("_leafEventTarget"));

        if ($targetWithoutIndexes == $this->model->leafPath) {
            $requestTargetParts = explode("_", $request->post("_leafEventTarget"));
            $pathParts = explode("_", $this->model->leafPath);

            if (preg_match('/\(([^)]+)\)/', $requestTargetParts[count($pathParts) - 1], $match)) {
                $this->setIndex($match[1]);
            }

            $eventName = $request->post("_leafEventName");
            $eventTarget = $request->post("_leafEventTarget");

            $eventArguments = [];

            if ($request->post("_leafEventArguments")) {
                $args = $request->post("_leafEventArguments");
                foreach ($args as $argument) {
                    $eventArguments[] = json_decode($argument);
                }
            }

            if ($request->post("_leafEventArgumentsJson")) {
                array_push($eventArguments, json_decode($request->post("_leafEventArgumentsJson"), true));
            }

            // Provide a callback for the event processing.
            $eventArguments[] = function ($response) use ($eventName, $eventTarget) {
                if ($response === null) {
                    return;
                }

                $type = "";

                if (is_object($response) || is_array($response)) {
                    $response = json_encode($response);
                    $type = ' type="json"';
                }

                print '<eventresponse event="' . $eventName . '" sender="' . $eventTarget . '"' . $type . '>
<![CDATA[' . $response . ']]>
</eventresponse>';
            };

            // First raise the event on the presenter itself
            $this->runBeforeRender(function () use ($eventName, $eventArguments) {
                $eventProperty = $eventName . "Event";

                if (property_exists($this->model, $eventProperty)) {
                    /**
                     * @var Event $event
                     */
                    $event = $this->model->$eventProperty;
                    return $event->raise(...$eventArguments);
                }

                return null;
            });
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

    /**
     * Called just before the view is rendered.
     */
    protected function beforeRender()
    {

    }

    /**
     * Called after events have finished processing but before beforeRender()
     */
    protected function afterEvents()
    {

    }

    public final function reRender()
    {
        $this->reRender = true;
    }

    private final function render()
    {
        $this->runBeforeRenderCallbacks();
        $this->afterEvents();
        $this->beforeRender();
        $html = $this->view->renderContent();

        return $html;
    }

    private final function renderXhr()
    {
        ob_start();

        $this->runBeforeRenderCallbacks();
        $this->afterEvents();
        $this->beforeRender();

        $xml = ob_get_clean();
        $xml .= $this->recursiveReRender();

        $xml = '<?xml version="1.0"?>
<leaf>
' . $xml;

        $xml .= '
</leaf>';

        return $xml;
    }

    public final function recursiveReRender()
    {
        if ($this->reRender) {
            $html = $this->render();
            $html = '<htmlupdate id="' . $this->model->leafPath . '">
<![CDATA[' . $html . ']]>
</htmlupdate>';

            return $html;
        } else {
            // Note that we don't need to call RecursiveRePresent if we are RePresenting ourselves
            // as that will naturally re present all sub presenters.

            return $this->view->recursiveReRender();
        }
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

        if ($request->header("Accept") == "application/leaf") {
            $response = new XmlResponse($this);
            $response->setContent($this->renderXhr());
        } else {
            $response = new HtmlResponse($this);
            $response->setContent($this->render());
        }

        return $response;
    }

    function __toString()
    {
        $levelBefore = ob_get_level();
        try {
            return $this->render();
        } catch (\Throwable $er) {
            $levelAfter = ob_get_level();
            while ($levelAfter > $levelBefore) {
                ob_end_clean();
                $levelAfter--;
            }
            Log::error("Unhandled " . basename(get_class($er)) . " `" . $er->getMessage() . "` in line " . $er->getLine() . " in " . $er->getFile(), 'ERROR');
            return $er->getMessage();
        }
    }

    private $runningEventsBeforeRender = false;

    /**
     * Register a callback to run just before leaf rendering takes place.
     * @param callable $callback
     */
    protected final function runBeforeRender(Callable $callback)
    {
        if ($this->runningEventsBeforeRender) {
            $callback();
        } else {
            $this->runBeforeRenderCallbacks[] = $callback;
        }
    }

    /**
     * Run the before render callbacks.
     */
    public function runBeforeRenderCallbacks()
    {
        $this->runningEventsBeforeRender = true;

        foreach ($this->runBeforeRenderCallbacks as $callback) {
            $callback();
        }

        $this->runBeforeRenderCallbacks = [];

        // Ask the view to notify sub leaves
        $this->view->runBeforeRenderCallbacks();

        $this->runningEventsBeforeRender = false;
    }

    /**
     * Provides this Leaf and the passed Leaf a chance to connect events should they recognise the
     * exposed events.
     *
     * @param Leaf $with The leaf with which to bind events.
     */
    public function bindEventsWith(Leaf $with)
    {
        $this->bindEvents($with);
        $with->bindEvents($this);
    }

    /**
     * A chance to discover if any of the events of the passed Leaf class are understood by this Leaf and
     * to attach a handler if appropriate.
     *
     * @param Leaf $with The Leaf to perform discovery on.
     */
    protected function bindEvents(Leaf $with)
    {
    }

    public function addCssClassNames(...$classNames)
    {
        $this->model->addCssClassNames(...$classNames);
    }

    public function removeCssClassNames(...$classNames)
    {
        $this->model->removeCssClassNames(...$classNames);
    }

    public function addHtmlAttribute($attributeName, $attributeValue)
    {
        $this->model->addHtmlAttribute($attributeName, $attributeValue);
    }

    public function removeHtmlAttribute($attributeName)
    {
        $this->model->removeHtmlAttribute($attributeName);
    }
}
