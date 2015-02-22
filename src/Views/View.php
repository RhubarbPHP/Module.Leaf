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

use Rhubarb\Crown\Context;
use Rhubarb\Crown\Deployment\Deployable;
use Rhubarb\Crown\Events\EventEmitter;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\PresenterDeploymentPackage;
use Rhubarb\Leaf\PresenterViewBase;
use Rhubarb\Stem\Models\Model;

/**
 * The base class for a View
 */
abstract class View extends PresenterViewBase implements Deployable
{
    private $eventReceiver;

    /**
     * A view can contain any number of presenters within it.
     *
     * @see View::AddPresenter()
     * @var Presenter[]
     */
    protected $presenters = array();

    protected $presenterName;

    protected $presenterPath;

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
        return $this->raiseEvent("GetIndexedPresenterPath");
    }

    protected function getData($key)
    {
        return $this->raiseEvent("GetData", $key);
    }

    /**
     * Gets model from the presenter.
     *
     * This should be used carefully - it is only intended to provide efficient access to the model for display,
     * business logic should not be performed in the View using this Model.
     *
     * @return null|Model
     */
    protected function getModel()
    {
        return $this->raiseEvent("GetModel");
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

    /**
     * Returns the deployment package required for this view.
     *
     * @return PresenterDeploymentPackage
     */
    public function getDeploymentPackage()
    {
        return null;
    }

    public function setName($viewName)
    {
        $this->presenterName = $viewName;
    }

    public function setPath($viewPath)
    {
        $this->presenterPath = $viewPath;
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
                $this->raiseEvent("OnPresenterAdded", $presenter);

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
    public final function processDelayedEvents()
    {
        foreach ($this->presenters as $presenter) {
            $presenter->processDelayedEvents();
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
     * Should return an array of Closures to use to wrap the content with.
     *
     *
     */
    protected function getWrappers()
    {
        return $this->wrappers;
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
            $context = new Context();

            if ($context->DeveloperMode) {
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
     * Allows the view to return persisted model data back to the presenter.
     *
     * Some views persist state between connections. As it is the view that is responsible for the perisistance, it
     * must also be responsible for the restoration of that data.
     *
     * @return array
     */
    public function getRestoredModel()
    {
        return [];
    }
}
