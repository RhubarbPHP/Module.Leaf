<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__ . "/../PresenterViewBase.php";

use Rhubarb\Crown\Application;
use Rhubarb\Crown\Events\Event;
use Rhubarb\Crown\Exceptions\ImplementationException;
use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\Logging\Log;
use Rhubarb\Crown\Modelling\ModelState;
use Rhubarb\Crown\Request\Request;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Response\GeneratesResponseInterface;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\Response\Response;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Exceptions\NoViewException;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;
use Rhubarb\Leaf\PresenterViewBase;
use Rhubarb\Leaf\Views\View;
/**
 * The base class for presenters
 *
 * @property string $PresenterName
 */
abstract class Leaf extends PresenterViewBase implements GeneratesResponseInterface
{
    /**
     * True if this presenter is the target of the invocation, false if it is a sub presenter.
     *
     * Note this is only set in the GenerateResponse() method and so will always be false in the constructor of the
     * presenter.
     *
     * @var bool
     */
    private $isExecutionTarget;

    /**
     * The model for this presenter
     *
     * Note that the model is public to allow unit tests to determine if the
     * presenter and view are working correctly.
     *
     * @var ModelState
     */
    public $model = null;

    /**
     * The view for this presenter
     *
     * This should only set to an interface so we can replace it for unit testing.
     *
     * @var View;
     */
    protected $view;

    /**
     * If set this will cause the view to be indexed with the relevant string
     *
     * e.g. Forename[2]
     *
     * @var string
     */
    protected $viewIndex = "";

    /**
     * Used to calculate the indexed presenter path of this presenter.
     * @var string
     */
    protected $parentIndexedPresenterPath = "";

    /**
     * True if the view has been configured.
     *
     * @var bool
     */
    private $initialised = false;

    /**
     * True if events have already been processed.
     *
     * @var bool
     */
    private $eventsProcessed = false;

    /**
     * @var string A name for the presenter
     */
    public $presenterName;

    /**
     * @var string The path within the hierarchy of sub presenters to identify this presenter.
     */
    public $presenterPath;

    /**
     * A collection of events to run after all other events have ran.
     *
     * This is normally used by controls on a view that need to run after all other
     * updates to the model have taken place.
     *
     * Events are queued by calling DelayEvent() and executed by ProcessDelayedEvents();
     *
     * @see Presenter::raiseDelayedEvent()
     * @see Presenter::processDelayedEvents()
     *
     * @var array
     */
    private $afterEventsCallbacks = [];

    /**
     * A count of the number of presenters hosted on our view.
     *
     * We use this to generate unique presenter names for each hosted presenter and that has to
     * be the responsibility of the presenter, not the view.
     *
     * @var int
     */
    private $hostedPresenterCount = 0;

    /**
     * Set to true if this presenter is hosted on another presenter.
     *
     * @var bool
     */
    protected $hosted = false;

    /**
     * Set to true if the presenter does not require the context of it's host in order to process RPC events.
     *
     * @var bool
     */
    protected $atomic = false;

    /**
     * True if when processing through AJAX this presenter should push it's view back to the client.
     *
     * This should only be set to true by calling RePresent()
     *
     * @see Presenter::rePresent()
     * @var bool
     */
    private $rePresent = false;

    /**
     * A collection of sub presenter names used on the view
     *
     * Used to ensure presenter names are unique - but only if there is more than
     * 1 on the view.
     *
     * @var array
     */
    private $subPresenterNamesUsed = [];

    /**
     * A collection of events to be called on the view bridge on an AJAX response.
     *
     * @var array
     */
    protected static $viewBridgeEvents = [];

    /**
     * If we need the presenter to be available but not actually printed
     * @var bool
     */
    public $suppressContent = false;

    /**
     * @param string $name Defaults to the class name
     */
    public function __construct($name = "")
    {
        if ($name == '') {
            // Set a default presenter name of the class name
            $name = StringTools::getShortClassNameFromNamespace(static::class);
        }

        $this->model = $this->createModel();
        $this->model->presenterName = $name;
        $this->model->presenterPath = $name;

        $this->initialise();
    }

    /**
     * The overriding class should implement to return a model class that extends PresenterModel
     *
     * This is normally done with an anonymous class for convenience
     *
     * @return LeafModel
     */
    protected abstract function createModel();

    /**
     * Returns the unique path to identify this presenter amongst the hierarchy of sub presenters forming the complete view.
     *
     * Presenter paths become import where state storage and AJAX post backs are involved.
     *
     * @return string
     */
    public function getPresenterPath()
    {
        return $this->model->presenterPath;
    }

    /**
     * Returns true to indicate this presenter is flagged for representing
     *
     * Used for unit testing.
     *
     * @return bool
     */
    public function needsRePresented()
    {
        return $this->rePresent;
    }

    /**
     * Sets the presenter path.
     *
     * This method is private as only a hosting presenter should be able to set this.
     *
     * @param $path
     */
    protected function setPresenterPath($path)
    {
        $this->model->presenterPath = $path;
    }

    /**
     * Gets the name, if any, of this presenter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->model->presenterName;
    }

    /**
     * Allows the presenter name to be changed.
     *
     * Only used internally to make sure presenter names are unique.
     *
     * @see setSubPresenterPath()
     * @param $presenterName
     */
    protected function setName($presenterName)
    {
        $this->model->presenterName = $presenterName;
    }

    /**
     * Delays code execution ntil after all events have processed.
     *
     * This is normally used by controls on a view that need to run after all other
     * updates to the model have taken place.
     *
     * Events are queued by calling runAfterEventsProcessed() and executed by processAfterEventsCallbacks();
     *
     * @see Presenter::processAfterEventsCallbacks()
     * @param Callable Normally a closure to run.
     * @return void
     */
    protected function runAfterEventsProcessed($event)
    {
        $this->afterEventsCallbacks[] = $event;
    }

    /**
     * Executes events delayed with DelayEvent
     *
     * @see Presenter::raiseDelayedEvent()
     */
    public final function processAfterEventsCallbacks()
    {
        foreach ($this->afterEventsCallbacks as $callback) {
            $callback();
        }

        $this->afterEventsCallbacks = [];

        $this->view->processAfterEventsCallbacks();
    }

    /**
     * Creates and sets a sub presenters path.
     *
     * @param Leaf $subPresenter
     */
    private function setSubPresenterPath(Leaf $subPresenter)
    {
        $this->hostedPresenterCount++;

        $subPresenterName = $subPresenter->getName();

        if ($subPresenterName == "" || in_array($subPresenterName, $this->subPresenterNamesUsed)) {
            $subPresenterName .= $this->hostedPresenterCount;
            $subPresenter->setName($subPresenterName);
        }

        $path = $this->getPresenterPath() . "_" . $subPresenterName;
        $subPresenter->setPresenterPath($path);

        $this->subPresenterNamesUsed[] = $subPresenterName;
    }

    /**
     * @var Leaf[]
     */
    protected $subPresenters = [];

    public function addSubPresenter(Leaf $presenter)
    {
        $this->setSubPresenterPath($presenter);

        try {
            $presenter->initialise();
        } catch (RequiresViewReconfigurationException $er) {
            // Some presenters can throw the RequiresViewReconfigurationException during their intialisation
            // e.g. BackgroundTaskFullFocusPresenter
            // However as we're still in the middle of the initialisation we don't need to handle it here
        }

        $presenter->parentIndexedPresenterPathChanged($this->model->indexedPresenterPath);
        $presenter->hosted = true;
        $presenter->onHosted();

        $this->onPresenterAdded($presenter);

        $this->subPresenters[$presenter->getName()] = $presenter;

        if ($presenter instanceof BindableLeafInterface){
            $presenter->bindingValueChangedEvent = new Event();
            $name = $presenter->getName();
            $presenter->bindingValueChangedEvent->attachHandler(function() use ($presenter, $name)
            {
                $bindingValue = $presenter->getBindingValue();

                if ($this->viewIndex){
                    $this->model->$name[$this->viewIndex] = $bindingValue;
                } else {
                    $this->model->$name = $bindingValue;
                }
            });

            if (isset($this->model->$name)){
                if ($this->viewIndex){
                    $presenter->setBindingValue($this->model->$name[$this->viewIndex]);
                } else {
                    $presenter->setBindingValue($this->model->$name);
                }

            }
        }

        return $presenter;
    }

    /**
     * Provides an opportunity for presenters to create sub presenters for their view based on a string name
     *
     * @param $presenterName
     * @return null
     */
    protected function createPresenterByName($presenterName)
    {
        return null;
    }

    public final function parentIndexedPresenterPathChanged($parentIndexedPresenterPath)
    {
        $this->parentIndexedPresenterPath = $parentIndexedPresenterPath;
        $this->calculateIndexedPresenterPath();
    }

    protected final function calculateIndexedPresenterPath()
    {
        $path = $this->parentIndexedPresenterPath;

        if ($path !== null) {
            $path .= "_" . $this->model->presenterName;
        } else {
            $path = $this->model->presenterName;
        }

        if (($this->viewIndex !== null) && ($this->viewIndex !== "")) {
            $path .= "(" . $this->viewIndex . ")";
        }

        $this->model->indexedPresenterPath = $path;

        foreach($this->subPresenters as $subPresenter){
            $subPresenter->parentIndexedPresenterPathChanged($path);
        }
    }

    /**
     * Attaches the view to the presenter.
     *
     * @param View $view
     */
    protected final function registerView(View $view)
    {
        $view->setModel($this->model);
        $view->presenterAddedEvent->attachHandler(function(Leaf $presenter){
            $this->addSubPresenter($presenter);
        });

        $this->view = $view;

        $this->onViewRegistered();
    }

    /**
     * Triggers an event on the client side for this presenter.
     *
     * Note that this approach should be seldom used as it violates a core principle of MVP; that the presenter
     * should not know any specifics about the view. If you're attempting to raise an event on the view bridge you
     * are coupling this presenter with a specific view and so there are usually better approaches to the problem.
     *
     * @param $presenterPath
     * @param $eventName
     */
    public static function raiseEventOnViewBridge($presenterPath, $eventName)
    {
        self::$viewBridgeEvents[] = func_get_args();
    }

    /**
     * Returns an array of state data which the view bridge can use
     *
     * @return array
     */
    protected function getModelState()
    {
        return $this->getPublicModelData();
    }

    protected function onViewRegistered()
    {

    }

    /**
     * Called when a presenter has been added to this presenters view.
     *
     * @param Leaf $presenter
     */
    protected function onPresenterAdded(Leaf $presenter)
    {

    }

    /**
     * Called once the presenter has been successfully added to a hosting presenter's view.
     *
     * Can be used to initiate activity that requires event hookups to be in place.
     */
    protected function onHosted()
    {
    }

    /**
     * Override to initialise the presenter with it's model, and any other relevant settings.
     *
     * The view should not be instantiated or configured here however - do this in ApplyModelToView
     */
    protected function initialiseModel()
    {

    }

    /**
     * Pass Display Identifier from View
     *
     * @return string
     */
    public function getDisplayIdentifier()
    {
        return $this->view->getDisplayIdentifier();
    }

    /**
     * Replaces the view with a version suitable for unit testing.
     *
     * @param View $mockView
     */
    public final function attachMockView(View $mockView)
    {
        $this->registerView($mockView);
        $this->initialise();
    }

    /**
     * Called to create and register the view.
     *
     * The view should be created and registered using RegisterView()
     * Note that this will not be called if a previous view has been registered.
     *
     * @see Presenter::registerView()
     * @return View
     */
    protected function createView()
    {
        return null;
    }

    /**
     * Call to make sure this presenter pushes it's view back to the client.
     */
    public function rePresent()
    {
        $this->rePresent = true;
    }

    public static $rePresenting = false;

    /**
     * Your implementation should create and configure a view class
     * and call it's PrintContent() method
     */
    protected function present()
    {
        if ($this->view == null) {
            throw new ImplementationException("Your presenter has no view.");
        }

        $this->model->isRootPresenter = $this->isExecutionTarget && !$this->hosted;

        $this->beforeRenderView();
        print $this->view->renderView();
    }

    /**
     * Called just before the view is rendered.
     *
     * Guaranteed to only be called once during a normal page execution.
     */
    protected function beforeRenderView()
    {

    }

    /**
     * Parses the request for any command actions.
     *
     * @param WebRequest $request
     */
    protected function parseRequestForCommand()
    {

    }

    /**
     * Determines if any events are due for processing.
     *
     * Also asks the view to do the same.
     */
    public final function processUserInterfaceEvents()
    {
        if (!$this->initialised) {
            // The presenter must be initialised before we can process events.
            $this->initialise();
        }

        $this->processStartupEvents();

        $path = $this->model->indexedPresenterPath;

        /**
         * @var WebRequest $request
         */
        $request = Request::current();

        $postData = isset($request->postData) && is_array($request->postData) ? $request->postData : [];
        $filesData = isset($request->filesData) && is_array($request->filesData) ? $request->filesData : [];

        $postData = array_merge($postData, $filesData);

        $indexes = [];

        foreach (array_keys($postData) as $key) {
            // Look for a pattern like Presenter_Path(3)
            if (preg_match('/^' . $path . '\(([^)]+)\)/', $key, $match)) {
                $matchingIndex = $match[1];

                if (!in_array($matchingIndex, $indexes)) {
                    $indexes[] = $matchingIndex;
                }
            }
        }

        $request = Request::current();

        if (sizeof($indexes) > 0) {
            foreach ($indexes as $index) {
                $this->viewIndex = $index;

                $this->parseRequestForCommand($request);
                $this->view->processUserInterfaceEvents();
                $this->parseRequestForEvent();
            }

            $this->viewIndex = "";
        } else {
            $this->parseRequestForCommand($request);
            $this->view->processUserInterfaceEvents();
            $this->parseRequestForEvent();
        }
    }

    /**
     * Looks for an event that should be raised on this presenter within the HTTP request data.
     *
     * An event is recognised if the _mvpEventTarget matches this presenter's path. If it does
     * the event name should be stored in _mvpEventName
     */
    private function parseRequestForEvent()
    {
        if (!isset($_REQUEST["_mvpEventTarget"])) {
            return;
        }

        $targetWithoutIndexes = preg_replace('/\([^)]+\)/', "", $_REQUEST["_mvpEventTarget"]);

        if (stripos($targetWithoutIndexes, $this->model->presenterPath) !== false) {
            $requestTargetParts = explode("_", $_REQUEST["_mvpEventTarget"]);
            $pathParts = explode("_", $this->model->presenterPath);

            if (preg_match('/\(([^)]+)\)/', $requestTargetParts[count($pathParts) - 1], $match)) {
                $this->viewIndex = $match[1];
            }
        }

        if ($targetWithoutIndexes == $this->model->presenterPath) {
            $eventName = $_REQUEST["_mvpEventName"];
            $eventTarget = $_REQUEST["_mvpEventTarget"];
            $eventArguments = [$eventName];

            if (isset($_REQUEST["_mvpEventArguments"])) {
                foreach ($_REQUEST["_mvpEventArguments"] as $argument) {
                    $eventArguments[] = json_decode($argument);
                }
            }

            if (isset($_REQUEST["_mvpEventArgumentsJson"])) {
                array_push($eventArguments, json_decode($_REQUEST["_mvpEventArgumentsJson"], true));
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
            call_user_func_array([$this, "raiseDelayedEvent"], $eventArguments);

            $this->view->setIndex($this->viewIndex);

            // Now raise the event on the view
            call_user_func_array([$this->view, "receivedEventPassThrough"], $eventArguments);
        }
    }

    /**
     * Manipulates the presenter name and path to allow multiple instances of the presenter to live on
     * the same page
     *
     * @param $index
     */
    public final function displayWithIndex($index)
    {
        $this->setViewIndex($index);
        print (string)$this;
    }

    protected final function setViewIndex($index)
    {
        $this->viewIndex = $index;
        $this->calculateIndexedPresenterPath();
    }

    /**
     * Manipulates the presenter name and path to allow multiple instances of the presenter to live on
     * the same page and returns the HTML for use in a host page.
     *
     * @param $index
     *
     * @return string
     */
    public final function getHtmlForIndex($index)
    {
        $this->viewIndex = $index;

        return (string) $this;
    }

    public final function __toString()
    {
        try {
            $response = $this->generateResponse();

            if ($response instanceof Response) {
                return $response->getContent();
            }

            return $response;
        } catch (\Exception $er) {
            Log::error("Unhandled " . basename(get_class($er)) . " `" . $er->getMessage() . "` in line " . $er->getLine() . " in " . $er->getFile(), 'ERROR');
            return $er->getMessage();
        }
    }

    public final function recursiveRePresent()
    {
        if ($this->rePresent) {
            $context = Application::current()->context();

            // Note we're bypassing the magic feature for performance.
            if (!$context->isXhrRequest()) {
                // If we're an ajax request and this presenter hasn't been asked to
                // re-present itself, we do nothing as that makes no sense.
                return;
            }

            ob_start();

            self::$rePresenting = true;
            $this->present();
            self::$rePresenting = false;

            $html = ob_get_clean();

            $html = '<htmlupdate id="' . $this->model->presenterPath . '">
<![CDATA[' . $html . ']]>
</htmlupdate>';

            print $html;
        } else {
            // Note that we don't need to call RecursiveRePresent if we are RePresenting ourselves
            // as that will naturally re present all sub presenters.

            $this->view->recursiveRePresent();
        }
    }

    /**
     * Initiates simplified processing to assist in unit testing.
     *
     * Execute this method if you have a presenter as a SUT that you need to invoke to perform your tests.
     *
     * @return string The view content printed as a consequence of the test.
     */
    public function test()
    {
        $this->initialise();

        try {
            $this->processEvents();
        } catch (RequiresViewReconfigurationException $er) {
            $this->initialiseView();
        }

        ob_start();

        $this->present();

        return ob_get_clean();
    }

    /**
     * Override this method to check user authorisation - return false if they are not permitted to view this presenter
     *
     * @return bool
     */
    protected function isPermitted()
    {
        return true;
    }

    /**
     * Returns the response for this Presenter
     *
     * Normally HTML.
     *
     * @param null|Request $request
     *
     * @throws PermissionException
     * @return string
     */
    public final function generateResponse($request = null)
    {

        $context = Application::current()->context();
        $isAjax = $context->isXhrRequest();

        // Make sure any event processing is deferred to the correct class if the class is specified.
        // This is used when the presenter is marked as atomic and so does not require the context of it's
        // parent to be able to handle the event.
        //
        // Should events be slower than necessary the first thing to consider is whether the presenter involved can
        // be flagged as atomic or redesigned so that it can be flagged as atomic.
        if ($isAjax && $request && ($className = $request->post("_mvpEventClass")) && $className != get_class($this)) {
            if (!$this->isPermitted()) {
                throw new PermissionException();
            }

            /** @var Leaf $correctPresenter */
            $correctPresenter = new $className();
            $correctPresenter->setPresenterPath($request->post("_mvpEventpresenterPath"));

            return $correctPresenter->generateResponse($request);
        }


        /** @var array $newState Keeps track of changes in any model or sub model. */
        $newState = [];

        // If $request is passed in we are being targeted by the request itself.
        $this->isExecutionTarget = ($request != null);

        if ($this->isExecutionTarget && !$this->isPermitted()) {
            throw new PermissionException();
        }

        $this->initialise();

        ob_start();

        if ($this->isExecutionTarget) {
            if ($isAjax) {
                print "<?xml version=\"1.0\"?><mvp>\r\n";
            }

            // Process events and if based on those events our view setup might change we
            // will reinitialise them and run the events again. This is because a new view
            // configuration may involve different presenters that will need a chance to run their
            // events. It's important therefore that if throwing the RequiresViewReconfiguration you
            // allow for other events also to be executed more than once (that would normally be quite
            // rare anyway).
            do {
                $continue = false;
                try {
                    $this->processEvents();
                } catch (RequiresViewReconfigurationException $er) {
                    $this->initialiseView(false);
                    $continue = true;
                }
            } while ($continue);
        }

        if ($this->isExecutionTarget && $isAjax) {
            $this->recursiveRePresent();
            $newState = $this->model->getState();
        } else {
            $this->present();
        }

        $html = ob_get_clean();

        $response = $this->onResponseGenerated($html);

        if ($response !== null && $response !== false) {
            $html = $response;
        }

        if ($isAjax) {
            $response = new HtmlResponse($this);

            if ($this->isExecutionTarget) {
                foreach ($newState as $path => $modelChange) {
                    $html .= "<model id=\"" . $path . "\"><![CDATA[" . json_encode($modelChange) . "]]></model>";
                }

                foreach (self::$viewBridgeEvents as $eventParams) {
                    $html .= '<event name="' . htmlentities($eventParams[1]) . '" target="' . htmlentities(
                            $eventParams[0]
                        ) . '">';

                    for ($i = 2; $i < sizeof($eventParams); $i++) {
                        $html .= '<param><![CDATA[' . $eventParams[$i] . ']]></param>';
                    }

                    $html .= '</event>';
                }

                $scripts = ResourceLoader::getResourceInjectionHtml();

                $html .= $scripts;

                $html .= "</mvp>";
            }

            $response->setContent($html);
            $response->setHeader("Content-Type", "text/xml");

            return $response;
        } else {
            // This allows raising viewbridge events as part of a full HTML response, so you can e.g.
            // provide data for events on first load of a page instead of having the page make a call
            // on load to retrieve data. Note that this should only be used when there's a specific
            // benefit to performing initial page setup through an Event instead of just outputting
            // your initial page HTML in the correct state.
            foreach (self::$viewBridgeEvents as $eventParams) {
                $target = json_encode(array_shift($eventParams));
                $eventParams = json_encode($eventParams);

                $javascript = <<<JS
                    var registeredPresenter = window.rhubarb.registeredPresenters[$target];
                    if (registeredPresenter) {
                        registeredPresenter.raiseClientEvent.apply(registeredPresenter, $eventParams);
                    }
JS;
                ResourceLoader::addScriptCode($javascript);
            }
        }

        return $html;
    }

    /**
     * Override this method to execute code after the response has been generated.
     *
     * It will have the opportunity to modify the return HTML by returning the adapted
     * HTML string.
     */
    protected function onResponseGenerated($html)
    {
        return false;
    }

    /**
     * Process events and then updates the view.
     */
    private function processEvents()
    {
        $this->processUserInterfaceEvents();
        $this->processAfterEventsCallbacks();
    }

    /**
     * Override to perform custom event firing during the presenters configuration stage.
     */
    protected function processStartupEvents()
    {

    }

    /**
     * Initialises the presenter's model, view and any hosted presenters
     */
    public final function initialise()
    {
        if (!$this->initialised) {
            $this->initialised = true;
            $this->initialiseModel();
            $this->model->indexedPresenterPath = $this->model->presenterPath;
            $this->initialiseView();
        }
    }

    /**
     * First creates and then configures the view.
     *
     * @see Presenter::createView()
     * @see Presenter::configureView()
     *
     * @param bool $andRestoreModel If true, the model will be restored after the view is initialised
     *
     * @throws NoViewException
     */
    protected function initialiseView($andRestoreModel = true)
    {
        $this->hostedPresenterCount = 0;
        $this->subPresenterNamesUsed = [];

        if (!$this->view) {
            $response = $this->createView();

            if ($response instanceof View) {
                $this->registerView($response);
            }
        }

        if (!$this->view) {
            throw new NoViewException();
        }


        if ($andRestoreModel) {
            // Now we have the view we can restore our model (the view is required to do this as the view is
            // responsible for encoding the public state of our model). We must do this before configureView()
            // as sometimes the restored model has a bearing on how our view will configure itself.
            //
            // We only do this if $andRestoreModel is set to true (default) as when we're representing we do
            // not want to restore the model again.
            $this->restoreModel();
        }

        $this->view->createPresenters();
    }

    /**
     * Provides an opportunity to restore model data before being used by the presenter.
     *
     * This allows traits to change the behaviour of the model setup without touching the
     * function hierarchy.
     *
     */
    protected function restoreModel()
    {
        $state = $this->view->getPropagatedState();

        $request = Request::current();
        $state = $request->post($this->model->presenterPath . "State");

        if ($state != null) {
            if (is_string($state)) {
                $this->model->restoreFromState(json_decode($state, true));
            }
        }
    }

    public function getRestoredModel()
    {
        $id = $this->model->presenterPath;


    }

    /**
     * Takes the data received from the hosting presenter and applies it to the model.
     *
     * @param $data
     */
    protected function applyBoundData($data)
    {
    }

    /**
     * Extracts data from the model and presents it to the hosting presenter for application to it's own model
     *
     * @return string
     */
    protected function extractBoundData()
    {
        return "";
    }

    /**
     * Get's the raw bound data from the hosting presenter.
     *
     * @return mixed|null
     */
    public final function fetchBoundData()
    {
        $data = $this->raiseEvent("GetBoundData", $this->model->presenterName, $this->viewIndex);

        if ($data !== null) {
            $this->applyBoundData($data);
        }

        return $data;
    }

    /**
     * Sends the bound data back to the hosting presenter.
     */
    public final function setBoundData()
    {
        $data = $this->extractBoundData();

        $this->raiseEvent("SetBoundData", $this->model->presenterName, $data, $this->viewIndex);
    }

    /**
     * Sets model data for a sub presenter.
     *
     * This implementation simply bubbles the event to this presenters host. Normal
     * practice is not to override this but instead use the ModelProvider trait which
     * does.
     *
     * @param string $dataKey
     * @param mixed $data
     * @param bool $viewIndex
     */
    protected function setDataFromPresenter($dataKey, $data, $viewIndex = false)
    {
        $this->raiseEvent("SetBoundData", $dataKey, $data, $viewIndex);
    }

    protected function setData($dataKey, $data, $viewIndex = false)
    {
        return $this->raiseEvent("SetData", $dataKey, $data, $viewIndex);
    }

    /**
     * Gets model data for a sub presenter.
     *
     * This implementation simply bubbles the event to this presenters host. Normal
     * practice is not to override this but instead use the ModelProvider trait which
     * does.
     *
     * @param string $dataKey
     * @param bool $viewIndex
     * @return mixed|null
     */
    protected function getDataForPresenter($dataKey, $viewIndex = false)
    {
        return $this->raiseEvent("GetBoundData", $dataKey, $viewIndex);
    }

    /**
     * Gets model data
     * @param      $dataKey
     * @param bool $viewIndex
     *
     * @return mixed|null
     */
    protected function getData($dataKey, $viewIndex = false)
    {
        return $this->raiseEvent("GetData", $dataKey, $viewIndex);
    }

    /**
     * Gets model
     *
     * @return null|Model
     */
    protected function getModel()
    {
        return $this->raiseEvent("GetModel");
    }

    /**
     * Provides access to the presenter's model
     *
     * @param $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        return $this->model[$name];
    }

    /**
     * Provides access to the presenter's model
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->model[$name] = $value;
    }

    /**
     * Override this to attach events to another presenter
     *
     * @param Leaf $presenter
     */
    protected function bindEvents(Leaf $presenter)
    {

    }

    /**
     * Provides an easy way for a presenter to bind events to another presenter.
     *
     * This method provides both parties with a chance to register event handlers.
     *
     * @param Leaf $presenter
     */
    public final function bindEventsWith(Leaf $presenter)
    {
        $this->bindEvents($presenter);
        $presenter->bindEvents($this);
    }

    public function getViewIndex()
    {
        return $this->viewIndex;
    }
}
