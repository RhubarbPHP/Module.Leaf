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

use Rhubarb\Crown\Context;
use Rhubarb\Crown\Exceptions\ImplementationException;
use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\Response\GeneratesResponse;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;
use Rhubarb\Leaf\PresenterViewBase;
use Rhubarb\Leaf\Views\View;
use Rhubarb\Stem\Exceptions\ModelConsistencyValidationException;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Models\Validation\Validator;

/**
 * The base class for presenters
 */
abstract class Presenter extends PresenterViewBase implements GeneratesResponse
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
     * Note that the model is public to allow for unit tests to determine if the
     * presenter and view are working correctly.
     *
     * @var \Rhubarb\Crown\Modelling\ModelState
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
    private $delayedEvents = array();

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
     * An associative array of any validators that have err'd.
     *
     * @var array
     */
    private $validationErrors = [];

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

    public function __construct($name = "")
    {
        $this->model = new PresenterModel();
        $this->model->PresenterName = $name;
        $this->model->PresenterPath = $name;
    }


    /**
     * Returns the unique path to identify this presenter amongst the hierarchy of sub presenters forming the complete view.
     *
     * Presenter paths become import where state storage and AJAX post backs are involved.
     *
     * @return string
     */
    public function getPresenterPath()
    {
        return $this->model->PresenterPath;
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
        $this->model->PresenterPath = $path;
    }

    /**
     * Gets the name, if any, of this presenter.
     *
     * @return string
     */
    public function getName()
    {
        return $this->model->PresenterName;
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
        $this->model->PresenterName = $presenterName;
    }

    public function getValidationErrorsByName($name)
    {
        $returnErrors = [];

        if (isset($this->validationErrors[$name])) {
            $returnErrors[] = $this->validationErrors[$name];
        }

        return $returnErrors;
    }

    /**
     * Performs the validation supplied and if it errors, stores the resultant error in the $validationErrors array.
     *
     * @param \Rhubarb\Stem\Models\Validation\Validator $validator
     * @return bool True if the validation succeeded. False if it didn't
     */
    public function validate(Validator $validator)
    {
        try {
            $validator->Validate($this->model);

            return true;
        } catch (ModelConsistencyValidationException $er) {
            $this->validationErrors[] = $er->getErrors();
        }

        return false;
    }

    /**
     * Delays an event until after other events have processed.
     *
     * This is normally used by controls on a view that need to run after all other
     * updates to the model have taken place.
     *
     * Events are queued by calling DelayEvent() and executed by ProcessDelayedEvents();
     *
     * @see Presenter::processDelayedEvents()
     * @param string $event The event code
     * @return void
     */
    protected function raiseDelayedEvent($event)
    {
        $args = func_get_args();

        $this->delayedEvents[] = $args;
    }

    /**
     * Executes events delayed with DelayEvent
     *
     * @see Presenter::raiseDelayedEvent()
     */
    public final function processDelayedEvents()
    {
        foreach ($this->delayedEvents as $event) {
            call_user_func_array(array($this, "raiseEvent"), $event);
        }

        $this->delayedEvents = array();

        $this->view->processDelayedEvents();
    }

    protected function createDefaultValidator()
    {
        // Empty validator that will always pass.
        return new Validator();
    }

    /**
     * Creates and sets a sub presenters path.
     *
     * @param Presenter $subPresenter
     */
    private function setSubPresenterPath(Presenter $subPresenter)
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

    protected $subPresenters = [];

    public function addSubPresenter(Presenter $presenter)
    {
        $this->setSubPresenterPath($presenter);

        $presenter->attachEventHandler(
            "GetIndexedPresenterPath",
            function () {
                return $this->getIndexedPresenterPath();
            }
        );

        $presenter->attachEventHandler(
            "GetBoundData",
            function ($dataKey, $viewIndex = false) {
                return $this->getDataForPresenter($dataKey, $viewIndex);
            }
        );

        $presenter->attachEventHandler(
            "GetData",
            function ($dataKey, $viewIndex = false) {
                return $this->getData($dataKey, $viewIndex);
            }
        );

        $presenter->attachEventHandler(
            "GetModel",
            function () {
                return $this->getModel();
            }
        );

        $presenter->attachEventHandler(
            "SetData",
            function ($dataKey, $data, $viewIndex = false) {
                $this->setData($dataKey, $data, $viewIndex);
            }
        );

        $presenter->attachEventHandler(
            "SetBoundData",
            function ($dataKey, $data, $viewIndex = false) {
                $this->setDataFromPresenter($dataKey, $data, $viewIndex);
            }
        );

        $presenter->initialise();
        $presenter->hosted = true;
        $presenter->onHosted();

        $this->onPresenterAdded($presenter);

        $this->subPresenters[$presenter->getName()] = $presenter;

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

    public final function getIndexedPresenterPath()
    {
        $path = $this->raiseEvent("GetIndexedPresenterPath");

        if ($path !== null) {
            $path .= "_" . $this->PresenterName;
        } else {
            $path = $this->PresenterName;
        }

        if (($this->viewIndex !== null) && ($this->viewIndex !== "")) {
            $path .= "(" . $this->viewIndex . ")";
        }

        return $path;
    }

    /**
     * Attaches the view to the presenter.
     *
     * @param View $view
     */
    protected final function registerView(View $view)
    {
        $this->view = $view;

        $this->view->setName($this->model->PresenterName);
        $this->view->setPath($this->model->PresenterPath);

        $view->attachEventHandler(
            "CreatePresenterByName",
            function ($presenterName) {
                return $this->createPresenterByName($presenterName);
            }
        );

        $view->attachEventHandler(
            "GetData",
            function ($dataKey, $viewIndex = false) {
                return $this->getData($dataKey, $viewIndex);
            }
        );
        $view->attachEventHandler(
            "GetModel",
            function () {
                return $this->getModel();
            }
        );

        $view->attachEventHandler(
            "GetIndexedPresenterPath",
            function () {
                return $this->getIndexedPresenterPath();
            }
        );

        $view->attachEventHandler(
            "OnPresenterAdded",
            function (Presenter $presenter) {
                return $this->addSubPresenter($presenter);
            }
        );

        $view->attachEventHandler(
            "GetValidationErrors",
            function ($validationName) {
                return $this->getValidationErrorsByName($validationName);
            }
        );

        $view->attachEventHandler(
            "IsRootPresenter",
            function () {
                return ($this->isExecutionTarget && !$this->hosted);
            }
        );

        $view->attachEventHandler(
            "GetEventHostClassName",
            function () {
                return $this->getEventHostClassName();
            }
        );

        $view->attachEventHandler(
            "GetModelState",
            function () {
                return $this->getModelState();
            }
        );

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
     * @param Presenter $presenter
     */
    protected function onPresenterAdded(Presenter $presenter)
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
     * Where relevant a presenter may realise another present is better doing the
     * work for the given request.
     */
    protected function getSubPresenter()
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
     * Override this to configure how your model is applied to your view.
     *
     * The view should be created first through CreateView()
     *
     * @see Presenter::createView()
     */
    protected function applyModelToView()
    {
        $this->view->setIndex($this->viewIndex);
    }

    public final function applyModelsToViews()
    {
        $this->applyModelToView();
        $this->view->applyModelsToViews();
        $this->onModelAppliedToView();
    }

    protected function onModelAppliedToView()
    {

    }

    /**
     * Returns the list of properties that should appear in the model.
     *
     * This does seem like duplicated effort as ModelState has a similar convention however the burden of creating
     * a separate model object for every presenter just to set this data is overkill
     *
     * @return array
     */
    protected function getPublicModelPropertyList()
    {
        return ["PresenterName", "PresenterPath"];
    }

    /**
     * Returns an array of model data permitted for sending to a client.
     *
     * @see getPublicModelPropertyList()
     * @return array
     */
    protected final function getPublicModelData()
    {
        $publicProperties = $this->getPublicModelPropertyList();
        $data = $this->model->exportRawData();

        $publicData = [];

        foreach ($publicProperties as $property) {
            if (isset($data[$property])) {
                $publicData[$property] = $data[$property];
            }
        }

        return $publicData;
    }

    /**
     * Returns true if the presenter has been configured such that it can't be re-instantiated perfectly
     * from the public model.
     *
     * @return bool
     */
    public function isConfigured()
    {
        $properties = $this->getPublicModelPropertyList();

        $data = $this->model->exportRawData();

        $keys = array_keys($data);

        $result = array_diff($keys, $properties);

        if (sizeof($result) > 0) {
            return true;
        }

        return $this->hasExternallyAttachedEventHandlers();
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
     */
    protected function createView()
    {

    }

    /**
     * Called to initialise the view.
     *
     * This method should be used to attach any event handlers. The view must first be created
     * using CreateView
     *
     * Do not apply any settings that might be overriden with default values in ApplyModelToView() or that
     * need to use the model. The reason for this is that after the view is initialised events are
     * processed that might change the model or view directly. Just before presenting the view we
     * call ApplyModelToView() to apply any remaining settings to the view (usually model data). If we apply the
     * model data too early it will be re-applied just before presentation with results that can sometimes
     * be hard to predict.
     *
     * @see Presenter::createView()
     * @see Presenter::UpdateView()
     */
    protected function configureView()
    {
        $this->view->suppressContent = $this->suppressContent;
    }

    public function setSuppressContent($suppress)
    {
        $this->view->suppressContent = $suppress;
        $this->suppressContent = $suppress;
    }

    /**
     * Call to make sure this presenter pushes it's view back to the client.
     */
    public function rePresent()
    {
        $this->rePresent = true;
    }

    protected function getEventHostClassName()
    {
        if ($this->atomic) {
            return get_class($this);
        }

        return "";
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

        $this->fetchBoundData();
        $this->beforeRenderView();
        $this->applyModelToView();
        $this->onModelAppliedToView();

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
     * Dispatches a command to a function that can deal with it.
     *
     * Functions to handle commands should be of the form Command{CommandName}
     * e.g CommandDeleteCustomer.
     *
     * This convention means all presenter commands are alphabetically grouped in the IDE
     * inspectors and guarantees the
     *
     * All arguments apart from the first are passed to the function.
     *
     * @param $command
     */
    public final function dispatchCommand($command)
    {
        if (!$this->initialised) {
            // Make sure the presenter is initialised.
            $this->initialise();
        }

        $functionName = "Command" . $command;

        $args = func_get_args();
        $args = array_slice($args, 1);

        if (method_exists($this, $functionName)) {
            call_user_func_array(array($this, $functionName), $args);
        }
    }

    /**
     * Parses the request for any command actions.
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

        $path = $this->getIndexedPresenterPath();

        $request = Context::currentRequest();

        $postData = is_array($request->PostData) ? $request->PostData : [];
        $filesData = is_array($request->FilesData) ? $request->FilesData : [];

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

        if (sizeof($indexes) > 0) {
            foreach ($indexes as $index) {
                $this->viewIndex = $index;

                $this->parseRequestForCommand();
                $this->view->processUserInterfaceEvents();
                $this->parseRequestForEvent();
            }

            $this->viewIndex = "";
        } else {
            $this->parseRequestForCommand();
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

        $targetWithoutIndexes = preg_replace("/\([^)]+\)/", "", $_REQUEST["_mvpEventTarget"]);

        if (stripos($targetWithoutIndexes, $this->model->PresenterPath) !== false) {
            $requestTargetParts = explode("_", $_REQUEST["_mvpEventTarget"]);
            $pathParts = explode("_", $this->model->PresenterPath);

            if (preg_match("/\(([^)]+)\)/", $requestTargetParts[count($pathParts) - 1], $match)) {
                $this->viewIndex = $match[1];
            }
        }

        if ($targetWithoutIndexes == $this->model->PresenterPath) {
            $eventName = $_REQUEST["_mvpEventName"];
            $eventTarget = $_REQUEST["_mvpEventTarget"];
            $eventArguments = [$eventName];

            if (isset($_REQUEST["_mvpEventArguments"])) {
                foreach ($_REQUEST["_mvpEventArguments"] as $argument) {
                    $eventArguments[] = json_decode($argument);
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
                    $type = " type=\"json\"";
                }

                print "<eventresponse event=\"" . $eventName . "\" sender=\"" . $eventTarget . "\"" . $type . ">
<![CDATA[" . $response . "]]>
</eventresponse>";
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
        $this->viewIndex = $index;

        print ( string )$this;
    }

    /**
     * Manipulates the presenter name and path to allow multiple instances of the presenter to live on
     * the same page and returns the HTML for use in a host page.
     *
     * @param $index
     */
    public final function getHtmlForIndex($index)
    {
        $this->viewIndex = $index;

        return ( string )$this;
    }

    public final function __toString()
    {
        try {
            $response = $this->generateResponse();

            if (!is_string($response)) {
                return $response->getContent();
            }

            return $response;
        } catch (\Exception $er) {
            return $er->getMessage();
        }
    }

    public final function getChangedPresenterModels()
    {
        $models = [];

        if ($this->model->hasChanged()) {
            $models[$this->getPresenterPath()] = $this->getPublicModelData();
        }

        $models = array_merge($models, $this->view->getChangedPresenterModels());

        return $models;
    }

    public final function recursiveRePresent()
    {
        if ($this->rePresent) {
            $context = new Context();

            // Note we're bypassing the magic feature for performance.
            if (!$context->getIsAjaxRequest()) {
                // If we're an ajax request and this presenter hasn't been asked to
                // re-present itself, we do nothing as that makes no sense.
                return;
            }

            ob_start();

            self::$rePresenting = true;
            $this->present();
            self::$rePresenting = false;

            $html = ob_get_clean();

            $html = "<htmlupdate id=\"" . $this->model->PresenterPath . "\">
<![CDATA[" . $html . "]]>
</htmlupdate>";

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
     * @param null $request
     *
     * @throws PermissionException
     * @return string
     */
    public final function generateResponse($request = null)
    {
        $context = new Context();

        $isAjax = $context->getIsAjaxRequest();

        // Make sure any event processing is deferred to the correct class if the class is specified.
        // This is used when the presenter is marked as atomic and so does not require the context of it's
        // parent to be able to handle the event.
        //
        // Should events be slower than necessary the first thing to consider is whether the presenter involved can
        // be flagged as atomic or redesigned so that it can be flagged as atomic.
        if ($isAjax && $request && ($className = $request->Post("_mvpEventClass")) && ($className != get_class(
                    $this
                ))
        ) {
            if (!$this->isPermitted()) {
                throw new PermissionException();
            }

            $correctPresenter = new $className();
            $correctPresenter->setPresenterPath($request->Post("_mvpEventPresenterPath"));

            return $correctPresenter->generateResponse($request);
        }


        /** @var array $modelChanges Keeps track of changes in any model or sub model. */
        $modelChanges = [];

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

            try {
                $this->processEvents();
            } catch (RequiresViewReconfigurationException $er) {
                $this->initialiseView();
            }
        }

        if ($this->isExecutionTarget && $isAjax) {
            $this->recursiveRePresent();
            $modelChanges = $this->getChangedPresenterModels();
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
                foreach ($modelChanges as $path => $modelChange) {
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
        $this->applyModelsToViews();
        $this->processUserInterfaceEvents();
        $this->processDelayedEvents();
    }

    /**
     * Initialises the presenter's model, view and any hosted presenters
     */
    public final function initialise()
    {
        if (!$this->initialised) {
            $this->initialised = true;
            $this->initialiseModel();
            $this->initialiseView();
            $this->restoreModel();

            // Snapshot the model so we can track if it changes during execution.
            $this->model->takeChangeSnapshot();
        }
    }

    /**
     * First creates and then configures the view.
     *
     * @see Presenter::createView()
     * @see Presenter::configureView()
     * @throws \Rhubarb\Leaf\Exceptions\NoViewException
     */
    protected function initialiseView()
    {
        if (!$this->view) {
            $response = $this->createView();

            if ($response instanceof View) {
                $this->registerView($response);
            }
        }

        if (!$this->view) {
            throw new \Rhubarb\Leaf\Exceptions\NoViewException();
        }

        $this->hostedPresenterCount = 0;

        $this->configureView();

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
        $restoredModelData = $this->view->getRestoredModel();

        $this->model->mergeRawData($restoredModelData);
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
        $data = $this->raiseEvent("GetBoundData", $this->model->PresenterName, $this->viewIndex);

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

        $this->raiseEvent("SetBoundData", $this->model->PresenterName, $data, $this->viewIndex);
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
     * @internal param \Rhubarb\Leaf\Presenters\Presenter $presenter
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
     * @param Presenter $presenter
     */
    protected function bindEvents(Presenter $presenter)
    {

    }

    /**
     * Provides an easy way for a presenter to bind events to another presenter.
     *
     * This method provides both parties with a chance to register event handlers.
     *
     * @param Presenter $presenter
     */
    public final function bindEventsWith(Presenter $presenter)
    {
        $this->bindEvents($presenter);
        $presenter->bindEvents($this);
    }
}
