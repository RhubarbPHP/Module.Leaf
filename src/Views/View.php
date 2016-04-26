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

namespace Rhubarb\Leaf\Views;

require_once __DIR__ . "/../PresenterViewBase.php";

use Rhubarb\Crown\Application;
use Rhubarb\Crown\Events\Event;
use Rhubarb\Crown\Deployment\Deployable;
use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\Request\Request;
use Rhubarb\Leaf\LayoutProviders\LayoutProvider;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\PresenterDeploymentPackage;
use Rhubarb\Leaf\Presenters\PresenterModel;
use Rhubarb\Leaf\PresenterViewBase;
use Rhubarb\Leaf\Views\Validation\Placeholder;

/**
 * The base class for a View
 */
abstract class View extends PresenterViewBase implements Deployable
{
    private $eventReceiver;

    /**
     * A view can contain any number of presenters within it.
     *
     * @see View::addPresenter()
     * @var Presenter[]
     */
    protected $presenters = [];

    /**
     * Wraps are closures which wrap the printed view content in additional supporting content (to support state etc.)
     *
     * @var \Closure[]
     */
    protected $wrappers = [];

    protected $index = "";

    /**
     * If we need the presenter to be available but not actually printed
     * @var bool
     */
    public $suppressContent = false;

    /**
     * True if the view requires the presenter state to be displayed as hidden inputs after the content.
     *
     * This can be set to false if:
     *
     * 1) You don't need to manipulate state (e.g. just displaying some data)
     * 2) The state is represented in some other form (e.g. a TextBox holds it's state in the textbox input)
     *
     * @var bool
     */
    protected $requiresStateInputs = true;

    /**
     * True if the view should output a container <DIV> tag surrounding the view.
     *
     * This is often required by client side view bridges however is sometimes unnecessary or unwanted.
     *
     * @var bool
     */
    protected $requiresContainer = true;

    /**
     * @var PresenterModel
     */
    protected $model;

    /**
     * @var Event
     */
    public $presenterAddedEvent;

    public function __construct()
    {
        $this->presenterAddedEvent = new Event();
    }

    public function setModel(PresenterModel $model)
    {
        $this->model = $model;
    }

    public function getDeploymentPackage()
    {
        $package = new PresenterDeploymentPackage();
        $package->resourcesToDeploy = [__DIR__ . "/ViewBridge.js"];

        $viewBridgeFilePath = $this->getClientSideViewBridgeFilePath();

        if ($viewBridgeFilePath) {
            $package->resourcesToDeploy[] = $viewBridgeFilePath;
        }

        return $package;
    }

    protected function getClientSideViewBridgeFilePath()
    {
        return false;
    }

    /**
     * Returns the name of the client side presenter bridge to attach to this presenter.
     *
     */
    protected function getClientSideViewBridgeName()
    {
        return "ViewBridge";
    }

    /**
     * Prints a collection of Inputs within a fieldset and <dl>
     *
     * This method takes a legend string and then any number of string or array parameters
     *
     * The string are outputted as HTML directly into the fieldset while the arrays contain
     * column information to rendered as a form.
     *
     * You can supply a simple array of column names:
     *
     * array( "Title", "Forename", "Surname" );
     *
     * Or you can return an associative array with the key being the label and the
     * value being either the string name of the field, or an actual input object.
     *
     * $title = new PgFormInputSelect( "Title" );
     * array( "Title" => $title, "Email Marketing" => "NoMail" );
     *
     * Or you can do both:
     *
     * array( "Title" => $title, "Forename", "Surname" );
     *
     * Additionally, if the key or value in the array cannot be mapped to a field input
     * the value will be treated as HTML and outputted directly. e.g.
     *
     * array( "<i>Wow HTML in a dd!</i>", "My Label" => "<b>My HTML</b>" );
     *
     * Note that while this base class provides SaveRecord and CancelRecord methods
     * for you to target with submitFunctions, you need to create and display the
     * save and cancel buttons yourself.
     *
     * The best practice is to create a standard base class for your project to provide
     * these and any other framing.
     *
     * @param mixed $legend The title of the field set. If blank, no legend will be outputted.
     */
    public function printFieldset($legend = "")
    {
        $args = func_get_args();

        $layout = $this->getBoundLayoutProvider();

        call_user_func_array([$layout, "printItemsWithContainer"], $args);
    }

    /**
     * Prints a group of controls
     *
     * @see printFieldset()
     */
    public function printControlGroup($inputs = [])
    {
        $args = func_get_args();

        $layout = $this->getBoundLayoutProvider();

        call_user_func_array([$layout, "printItems"], $args);
    }

    /**
     * Gets a LayoutProvider that is bound to this view.
     *
     * @return LayoutProvider
     */
    protected function getBoundLayoutProvider()
    {
        $layoutProvider = LayoutProvider::getDefaultLayoutProvider();
        $layoutProvider->setValueGenerationCallBack(
            function ($name) {
                return $this->getControlByName($name);
            }
        );

        $layoutProvider->setValidationPlaceholderGenerationCallBack(
            function ($placeholderName) {
                return new Placeholder($placeholderName, $this);
            }
        );

        return $layoutProvider;
    }

    /**
     * Generates a control presenter object or parses a template for controls
     * .
     * @param string $name A string representing a field name, control name or template string.
     * @return bool|\Rhubarb\Leaf\Presenters\Controls\ControlPresenter
     */
    protected final function getControlByName($name)
    {
        if (isset($this->presenters[$name])) {
            return $this->presenters[$name];
        }

        return false;
    }

    protected function getWrappers()
    {
        $wrappers = $this->wrappers;

        if ($this->requiresStateInputs) {
            $wrappers[] = function ($content) {
                $id = $this->getIndexedPresenterPath();

                $request = Request::current();
                $ajaxUrl = $request->urlPath;

                $viewIndexSuffix = ($this->index) ? "[_" . $this->index . "]" : "";

                $url = ($ajaxUrl) ? '
<input type="hidden" name="' . $id . 'Url" id="' . $id . 'Url" value="' . $ajaxUrl . '" />' : '';

                $html = $content . '
<input type="hidden" name="' . $id . 'State" id="' . $id . 'State" value="' . htmlentities(
                        json_encode($this->getState())
                    ) . '" />' . $url;

                $hostClassName = $this->raiseEvent("GetEventHostClassName");

                if ($hostClassName != "") {
                    $html .= '
	<input type="hidden" name="' . $id . 'EventHost" id="' . $id . 'EventHost" value="' . htmlentities(
                            $hostClassName
                        ) . '" />';
                }

                return $html;
            };
        }

        if ($this->requiresContainer) {
            $wrappers[] = function ($content) {
                $path = $this->getIndexedPresenterPath();
                $name = $this->model->presenterName;

                $classes = [basename(str_replace("\\", "/", get_class($this)))];

                if ($this->raiseEvent("IsRootPresenter")) {
                    $classes[] = "host";
                }

                $class = "";

                if (sizeof($classes)) {
                    $class = " class=\"" . implode(" ", $classes) . "\"";
                }

                $nameAttribute = ($name) ? " presenter-name=\"" . htmlentities($name) . "\"" : "";

                $html = '<div id="' . $path . '"' . $class . $nameAttribute . '>
' . $content . '
</div>';

                return $html;
            };
        }

        if ($this->model->isRootPresenter) {
            $formWrapper = $this->getFormWrapper();
            if ($formWrapper) {
                $wrappers[] = $formWrapper;
            }
        }

        $viewBridge = $this->getClientSideViewBridgeName();

        if ($viewBridge != "" && !Presenter::$rePresenting) {
            $indexedPath = $this->getIndexedPresenterPath();

            // Top level HTML Presenters sometimes don't have a path as they are the root item. In this case they can't
            // support a view bridge anyway so we can ignore this. This is actually quite rare as normally the top
            // level presenter will extend the Form presenter which does have a name and path.
            if ($indexedPath != "") {
                $wrappers[] = function () {
                    $deploymentPackage = $this->getDeploymentPackage();
                    $urls = $deploymentPackage->getDeployedUrls();
                    $urls = array_merge($this->getAdditionalResourceUrls(), $urls);

                    $jsAndCssUrls = [];

                    foreach ($urls as $url) {
                        if (preg_match("/\.js$/", $url) || preg_match("/\.css$/", $url)) {
                            $jsAndCssUrls[] = $url;
                        }
                    }

                    ResourceLoader::addScriptCode(
                        "new window.rhubarb.viewBridgeClasses." . $this->getClientSideViewBridgeName() . "( '" . $this->getIndexedPresenterPath() . "' );",
                        $jsAndCssUrls
                    );
                };
            }
        }

        return $wrappers;
    }

    protected function getFormWrapper()
    {
        return function ($content) {
            return <<<HTML
<form method="post" enctype="multipart/form-data">
$content
</form>
HTML;
        };
    }

    protected final function raiseEventOnViewBridge($eventName)
    {
        $args = func_get_args();
        array_unshift($args, $this->model->presenterPath);
        call_user_func_array(['\Rhubarb\Leaf\Presenters\Presenter', "raiseEventOnViewBridge"], $args);
    }

    /**
     * Allows the view to return persisted model data back to the presenter.
     *
     * @return string[]
     */
    public function getPropagatedState()
    {
        $id = $this->model->presenterPath;

        $request = Request::current();
        $state = $request->post($id . "State");

        if ($state != null) {
            if (is_string($state)) {
                return json_decode($state, true);
            }
        }

        return [];
    }

    /**
     * @param string $index
     */
    public function setIndex($index)
    {
        $this->index = $index;
    }

    protected function addWrapper(\Closure $wrapClosure)
    {
        $this->wrappers[] = $wrapClosure;
    }

    /**
     * Returns an array of resource URLs this presenter depends on that do not require deployment.
     *
     * If you need to deploy a script or stylesheet override the GetDeploymentPackage() method instead.
     *
     * @return array
     */
    protected function getAdditionalResourceUrls()
    {
        return [];
    }

    /**
     * Returns the presenter path with a suffix to represent the index if it is in play
     *
     * e.g. Forename[2]
     */
    protected function getIndexedPresenterPath()
    {
        return $this->model->indexedPresenterPath;
    }

    /**
     * Gets the Display Identifier
     *
     * @return string
     */
    public function getDisplayIdentifier()
    {
        return $this->getIndexedPresenterPath();
    }

    public function setName($viewName)
    {
        $this->model->presenterName = $viewName;
    }

    public function setPath($viewPath)
    {
        $this->model->presenterPath = $viewPath;
    }

    public function registerEventReceiver(\Closure $receiver)
    {
        $this->eventReceiver = $receiver;
    }

    public final function getChangedPresenterModels()
    {
        $models = [];

        foreach ($this->presenters as $presenter) {
            $models = array_merge($models, $presenter->getChangedPresenterModels());
        }

        return $models;
    }

    /**
     * Recursively descends through the presenter tree and makes sure that all children have been updated
     * by their parent to reflect the current model state.
     */
    public final function applyModelsToViews()
    {
        $this->configurePresenters();

        foreach ($this->presenters as $presenter) {
            $presenter->applyModelsToViews();
        }
    }

    private final function createPresenterByName($presenterName)
    {
        return $this->raiseEvent("CreatePresenterByName", $presenterName);
    }

    public final function addPresenters($presenter)
    {
        $args = (isset($presenter) && is_array($presenter)) ? $presenter : func_get_args();

        foreach ($args as $index => $presenter) {
            if (is_string($presenter)) {
                $presenter = $this->createPresenterByName($presenter);
            }

            if ($presenter instanceof Presenter) {
                $this->onPresenterAdded($presenter);
                $this->presenterAddedEvent->raise($presenter);
                $name = (is_numeric($index)) ? $presenter->getName() : $index;

                $this->presenters[$name] = $presenter;
            }
        }
    }

    /**
     * An opportunity to configure presenters as they are added.
     *
     * This is most useful when presenters are being generated through automation e.g. model bindings.
     *
     * @param Presenter $presenter
     */
    protected function onPresenterAdded(Presenter $presenter)
    {
    }

    /**
     * Called to allow a view to instantiate any sub presenters that may be needed.
     *
     * Called by the presenter when it is ready to receive any corresponding events.
     */
    public function createPresenters()
    {

    }

    /**
     * Called just before a view is printed.
     *
     * Allows a view to update the sub presenters to reflect the current state of the view/presenter/model.
     */
    protected function configurePresenters()
    {

    }

    protected function parseRequestForCommand()
    {

    }

    /**
     * While views can't raised delayed events their hosted presenters can.
     *
     */
    public final function processAfterEventsCallbacks()
    {
        foreach ($this->presenters as $presenter) {
            $presenter->processAfterEventsCallbacks();
        }
    }

    public final function recursiveRePresent()
    {
        foreach ($this->presenters as $presenter) {
            $presenter->recursiveRePresent();
        }
    }

    /**
     * During post back parsing, this will look to see if view indexes are being used for this presenter
     *
     * If so the view indexes will be extracted and subsequent event processing will be done for each
     * and every index.
     */
    private final function checkForViewIndexInRequest()
    {

    }

    /**
     * Processes the request for events and asks any hosted presenters to do the same.
     */
    public final function processUserInterfaceEvents()
    {
        $this->parseRequestForCommand();

        foreach ($this->presenters as $presenter) {
            $presenter->processUserInterfaceEvents();
        }
    }

    protected function getState()
    {
        return $this->raiseEvent("GetModelState");
    }

    /**
     * An opportunity for an extender or a trait to perform some last minute manipulation
     *
     * If this method returns false, we cancel the call the PrintViewContent()
     */
    protected function onBeforePrintViewContent()
    {

    }

    public final function renderView()
    {
        $this->configurePresenters();

        $deploymentPackage = $this->getDeploymentPackage();

        if ($deploymentPackage != null) {
            // If we're in developer mode - make the deployment
            $application = Application::current();

            if ($application->developerMode) {
                $deploymentPackage->deploy();
            }
        }

        ob_start();
        if (!$this->suppressContent) {
            // Allow super classes and traits to do their own interception and printing.
            $result = $this->onBeforePrintViewContent();

            // Should the super class return false we know it has handled the output already.
            if ($result !== false) {
                $this->printViewContent();
            }
        }

        $viewContent = ob_get_clean();

        $wrappers = $this->getWrappers();

        foreach ($wrappers as $wrap) {
            $newContent = $wrap($viewContent);

            // Allow for wrappers that don't return new content but just have other side effects
            // like throwing events or requiring resources
            if ($newContent !== null) {
                $viewContent = $newContent;
            }
        }

        return $viewContent;
    }

    protected function printViewContent()
    {
        print "";
    }

    /**
     * Returns any validation errors for the given validation name.
     *
     * Used primarily by validation placeholders and acts as a middle man between the same method on the presenter.
     *
     * @param $validationName
     * @return array
     */
    public function getValidationErrors($validationName)
    {
        return $this->raiseEvent("GetValidationErrors", $validationName);
    }

    /**
     * Returns content to be displayed in the placeholder for the given validation name before there are any
     * validation errors to display. The intent is for this to indicate required fields.
     *
     * @param $validationName
     * @return mixed|null
     */
    public function getPlaceholderDefaultContent($validationName)
    {
        return $this->raiseEvent("GetPlaceholderDefaultContent", $validationName);
    }
}
