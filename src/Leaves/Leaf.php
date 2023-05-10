<?php

namespace Rhubarb\Leaf\Leaves;

use Rhubarb\Crown\DependencyInjection\Container;
use Rhubarb\Crown\Events\Event;
use Rhubarb\Crown\Logging\Log;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Response\GeneratesResponseInterface;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\Response\XmlResponse;
use Rhubarb\Crown\Settings\WebsiteSettings;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Csrf\CsrfProtection;
use Rhubarb\Leaf\Exceptions\InvalidLeafModelException;
use Rhubarb\Leaf\Exceptions\NoViewException;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;
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
     * @var bool True to enable Cross Site Request Forgery validation
     *
     * DO NOT TURN THIS OFF unless you really really know what you're doing and have read the following
     * article completely:
     *
     * @link https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)_Prevention_Cheat_Sheet
     */
    private static $csrfValidation = true;

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

    /**
     * @var bool If true, objects in the request will be converted to PHP associative arrays. Otherwise they will be stdClass objects.
     */
    public $objectsToAssocArrays = false;

    /**
     * @param string $name A name used to reference the leaf. If not provided it will be automatically set to the class name (without namespace)
     * @param callable|null $initialiseModelBeforeView A callback which will be called before onModelCreated, allowing Leaf constructors to take
     *                                                 arguments which can be added to the model's data before the View is initialised.
     * @throws InvalidLeafModelException
     */
    public function __construct($name = null, callable $initialiseModelBeforeView = null)
    {
        $this->model = $this->createModel();

        if ($this->model === null || !($this->model instanceof LeafModel)) {
            throw new InvalidLeafModelException("The call to createModel on " . get_class($this) . " didn't return a LeafModel class");
        }

        if ($initialiseModelBeforeView) {
            $initialiseModelBeforeView($this->model);
        }

        if ($name == null) {
            $name = StringTools::getShortClassNameFromNamespace(static::class);
        }

        $this->setName($name);

        $this->onModelCreated();

        $this->initialiseView();
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
    final protected function initialiseView()
    {
        $view = $this->createView();

        $this->view = $view;
    }

    final protected function reconfigureView()
    {
        $this->view->reconfigure();
    }

    /**
     * Gets the name of the leaf.
     *
     * @see $name
     * @return string
     */
    final public function getName()
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
    final public function getModelForTesting()
    {
        return $this->model;
    }

    /**
     * Suppress containing form if root node
     */
    final public function suppressContainingForm()
    {
        $this->model->suppressContainingForm = true;
    }

    /**
     * Suppress name attribute on state hidden input. This stops the state being submitted as part of the form. Ideally
     * used when the leaf is being submitted as part of a get request and you do not want the state submitted in the
     * URL. If you do not need to propagate the state, please preference overriding the $requiresStateInput property
     * in your leaf instead.
     */
    final public function suppressStateInputNameAttribute()
    {
        $this->model->suppressStateInputNameAttribute = true;
    }

    /**
     * Sets the name of the leaf.
     *
     * @param $name string The new name for the leaf
     * @param $parentPath string The leaf path for the containing leaf
     */
    final public function setName($name, $parentPath = "")
    {
        $this->model->leafName = $name;

        if ($parentPath != "") {
            $this->model->parentPath = $parentPath;
            $this->model->isRootLeaf = false;
        } else {
            $this->model->parentPath = "";
        }

        $this->model->updatePath();

        if ($this->view) {
            $this->view->leafPathChanged();
        }
    }

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    abstract protected function getViewClass();

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    abstract protected function createModel();

    private function createView()
    {
        try {
            $view = Container::instance($this->getViewClass(), $this->model);
        } catch (\ReflectionException $er) {
            throw new NoViewException("The Leaf " . get_class($this) . " is not configured to use a valid View class. Check `getViewClass`");
        }

        return $view;
    }

    /**
     * Sets the web request being used to render the tree of leaves.
     *
     * @param WebRequest $request
     */
    final public function setWebRequest(WebRequest $request)
    {
        if (self::$csrfValidation && $request->server('REQUEST_METHOD') == 'POST'){
            CsrfProtection::singleton()->validateHeaders($request);
            CsrfProtection::singleton()->validateCookie($request);
        }

        $this->request = $request;

        $this->parseRequest($request);
        $this->view->setWebRequest($request);

        $this->model->onAfterRequestSet();
        $this->onStateRestored();
    }

    /**
     * Disables CSRF protection, only for very particular cases.
     *
     * DO NOT TURN THIS OFF unless you really really know what you're doing and have read the following
     * article completely:
     *
     * @link https://www.owasp.org/index.php/Cross-Site_Request_Forgery_(CSRF)_Prevention_Cheat_Sheet
     *
     * Once off, it will stay off for the duration of this Leaf and all it's subleaves.
     */
    protected static function disableCsrfProtection()
    {
        self::$csrfValidation = false;
    }

    protected function onStateRestored()
    {
    }

    /**
     * Parses the request looking for client side events.
     *
     * @param WebRequest $request
     */
    protected function parseRequest(WebRequest $request)
    {
        if ($this->model->isRootLeaf) {
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
                $this->model->leafIndex = $match[1];
                $this->model->updatePath();
            }

            $eventName = $request->post("_leafEventName");
            $eventTarget = $request->post("_leafEventTarget");

            $eventArguments = [];

            if ($request->post("_leafEventArguments")) {
                $args = $request->post("_leafEventArguments");
                foreach ($args as $argument) {
                    $eventArguments[] = json_decode($argument, $this->objectsToAssocArrays);
                }
            }

            if ($request->post("_leafEventArgumentsJson")) {
                $jsonArguments = json_decode($request->post("_leafEventArgumentsJson"), true);
                if (count($jsonArguments)) {
                    array_push($eventArguments, ...$jsonArguments);
                }
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
                    /** @var Event $event */
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
    final public function printWithIndex($index)
    {
        print $this->render($index);
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

    final public function reRender()
    {
        $this->reRender = true;
    }


    final public function deploy($viewIndex = null)
    {
        $this->view->deploy($viewIndex);
    }

    private function render($viewIndex = null)
    {
        $this->model->leafIndex = $viewIndex;

        $this->runBeforeRenderCallbacks();
        $this->afterEvents();
        $this->beforeRender();

        $html = $this->view->renderContent($viewIndex);

        return $html;
    }

    private function renderXhr()
    {
        ob_start();

        $this->runBeforeRenderCallbacks();
        $this->afterEvents();
        $this->beforeRender();

        $xml = ob_get_clean();
        $xml .= $this->recursiveReRender();
        $xml .= $this->recursivePushModelChanges();

        $xml = '<?xml version="1.0"?>
<leaf>
' . $xml;

        $xml .= '
</leaf>';

        return $xml;
    }

    /**
     * Recursively descends the tree of leaves and returns a string of model changes to push to the client.
     * @return string
     */
    final public function recursivePushModelChanges()
    {
        return $this->view->recursivePushModelChanges();
    }

    final public function recursiveReRender()
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
     * Renders the Leaf and returns an HtmlResponse to Rhubarb
     *
     * @param WebRequest|null $request
     * @return HtmlResponse|null
     */
    final public function generateResponse($request = null)
    {
        while (true) {
            $this->setWebRequest($request);

            try {
                if ($request->header("Accept") == "application/leaf") {
                    $response = new XmlResponse($this);
                    $response->setContent($this->renderXhr());
                } else {
                    $response = new HtmlResponse($this);
                    $response->setContent($this->render());
                }

                return $response;
            } catch (RequiresViewReconfigurationException $er) {
                $this->initialiseView();
            }
        }

        return null;
    }

    public function __toString()
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
    final protected function runBeforeRender(callable $callback)
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
