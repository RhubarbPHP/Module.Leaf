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

use Codeception\Lib\Interfaces\Web;
use Rhubarb\Crown\Application;
use Rhubarb\Crown\Deployment\DeploymentPackage;
use Rhubarb\Crown\Deployment\Deployable;
use Rhubarb\Crown\Events\Event;
use Rhubarb\Crown\Exceptions\RhubarbException;
use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\LayoutProviders\LayoutProvider;
use Rhubarb\Leaf\Leaves\BindableLeafInterface;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafDeploymentPackage;
use Rhubarb\Leaf\Leaves\LeafModel;

/**
 * The base class for a View
 */
class View implements Deployable
{
    /**
     * The shared model between leaf and view.
     *
     * @var LeafModel
     */
    protected $model;

    /**
     * The WebRequest we are generating a response for.
     *
     * @var WebRequest
     */
    private $request;

    /**
     * A named collection of sub leafs populated by calling registerSubLeaf
     *
     * @see registerSubLeaf()
     * @var Leaf[]
     */
    protected $leaves = [];

    /**
     * True if the leaf needs a hidden state input to propogate it's state.
     *
     * @var bool
     */
    protected $requiresStateInput = true;

    /**
     * True if the leaf needs a surrounding div for JS to target.
     *
     * @var bool
     */
    protected $requiresContainerDiv = true;

    /**
     * Tracks the number of times a leaf name has occurred for sub leafs
     *
     * This is used to ensure if two leaves get added with the same name, they are differentiated by a numerical suffix.
     *
     * @var int[]
     */
    private $namesUsed = [];

    /**
     * @var Event
     */
    private $beforeRenderEvent;

    public final function __construct(LeafModel $model)
    {
        $this->model = $model;
        $this->beforeRenderEvent = new Event();
        $this->createSubLeaves();
        $this->attachModelEventHandlers();
    }

    /**
     * An opportunity for extending classes to register handlers for model events.
     */
    protected function attachModelEventHandlers()
    {

    }

    public final function setWebRequest(WebRequest $request)
    {
        $this->request = $request;
        $this->restoreStateIntoModel();
        $this->parseRequest($request);

        foreach($this->leaves as $leaf){
            $leaf->setWebRequest($request);
        }
    }

    /**
     * Provides the extending class an opportunity to examine the incoming request and raise events if appropriate.
     *
     * @param WebRequest $request
     */
    protected function parseRequest(WebRequest $request)
    {
    }

    private final function restoreStateIntoModel()
    {
        $stateKey = $this->getStateKey();

        if ($this->request){
            $state = $this->request->post($stateKey);

            if ($state !== null) {
                $state = json_decode($state, true);

                if ($state) {
                    $this->model->restoreFromState($state);
                }
            }
        }
    }

    /**
     * Called by the view's leaf class when it's leaf path is changed.
     *
     * This cascades down all sub view and leaves.
     */
    public final function leafPathChanged()
    {
        foreach($this->leaves as $leaf){
            $leaf->setName($leaf->getName(), $this->model->leafPath);
        }
    }


    /**
     * The place where extending classes should create and register new Views
     */
    protected function createSubLeaves()
    {

    }

    public function runBeforeRenderCallbacks()
    {
        foreach($this->leaves as $leaf) {
            $leaf->runBeforeRenderCallbacks();
        }
    }

    /**
     * @param Leaf[] ...$subLeaves
     */
    protected final function registerSubLeaf(...$subLeaves)
    {
        foreach($subLeaves as $subLeaf) {

            // If the sub leaf isn't a Leaf but a string - we see if our Leaf host can create a leaf for us.
            // This facility allows for auto creation of control leaves for rapid form development in connection
            // with Stem models.
            if (is_string($subLeaf)){
                $response = $this->model->createSubLeafFromNameEvent->raise($subLeaf);

                if (!($response instanceof Leaf)){
                    continue;
                }

                $subLeaf = $response;
            }

            $name = $subLeaf->getName();

            if (isset($this->namesUsed[$name])) {
                $this->namesUsed[$name]++;
                $name .= $this->namesUsed[$name];
            } else {
                $this->namesUsed[$name] = 0;
            }

            $subLeaf->setName($name, $this->model->leafPath);
            $this->leaves[$name] = $subLeaf;

            if ($subLeaf instanceof BindableLeafInterface) {
                // Setup data bindings
                $event = $subLeaf->getBindingValueChangedEvent();

                $event->attachHandler(function ($index = null) use ($name, $subLeaf) {
                    $bindingValue = $subLeaf->getValue();
                    $this->model->setBoundValue($name, $bindingValue, $index);
                });

                $event = $subLeaf->getBindingValueRequestedEvent();
                $event->attachHandler(function($index = null) use ($name){
                    return $this->model->getBoundValue($name, $index);
                });
            }
        }
    }

    /**
     * @return DeploymentPackage
     */
    public function getDeploymentPackage()
    {
        if ($this->model->isRootLeaf){
            // If we're the root leaf, we're also the host for all client side view bridge events.
            // It's a real chore to have to define a view bridge just to allow child view bridges to
            // fire events.
            return new LeafDeploymentPackage();
        }

        return null;
    }

    protected function printViewContent()
    {

    }

    public final function recursiveReRender()
    {
        $response = "";

        foreach($this->leaves as $subLeaf){
            $response .= $subLeaf->recursiveReRender();
        }

        return $response;
    }

    private static $viewBridgeRegistrationCallback = null;

    /**
     * An opportunity for extending classes to perform setup before the view is rendered.
     */
    protected function beforeRender()
    {

    }

    public final function renderContent()
    {
        $allDeployedUrls = [];
        $viewBridges = [];

        ob_start();

        $oldCallback = self::$viewBridgeRegistrationCallback;

        self::$viewBridgeRegistrationCallback = function($viewBridgeName, $leafPath, $childViewBridges, $deployedUrls) use (&$viewBridges, &$allDeployedUrls){
            $viewBridges[$leafPath] = [ $viewBridgeName, $childViewBridges ];
            $allDeployedUrls = array_merge($allDeployedUrls, $deployedUrls);
        };

        $this->beforeRender();
        $this->beforeRenderEvent->raise();
        $this->printViewContent();

        self::$viewBridgeRegistrationCallback = $oldCallback;

        $content = ob_get_clean();

        $state = $this->model->getState();
        $state = json_encode($state);

        if ($this->requiresStateInput) {
            $content .= '
<input type="hidden" name="' . $this->getStateKey() . '" id="' . $this->getStateKey() . '" value="' . htmlentities($state) . '" />';
        }

        if ($this->requiresContainerDiv) {
            $viewBridge = ($this->getViewBridgeName()) ? ' leaf-bridge="'.$this->getViewBridgeName().'"' : '';
            $content = '<div leaf-name="'.$this->model->leafName.'" '.$viewBridge.' id="'.$this->model->leafPath.'"'.$this->model->getClassAttribute().'>'.$content.'</div>';
        }

        $content = $this->wrapViewContent($content);

        if ($this->model->isRootLeaf){
            $content = '
<form method="post" enctype="multipart/form-data">
'.$content.'
</form>
';
        }

        $resourcePackage = $this->getDeploymentPackage();
        $viewBridge = $this->getViewBridgeName();

        if ($viewBridge){

            if ($resourcePackage) {
                if (Application::current()->developerMode) {
                    $urls = $resourcePackage->deploy();
                } else {
                    $urls = $resourcePackage->getDeployedUrls();
                }

                $allDeployedUrls = array_merge($allDeployedUrls, $urls, $this->getAdditionalResourceUrls());
            }

            if (self::$viewBridgeRegistrationCallback != null){
                $callback = self::$viewBridgeRegistrationCallback;
                /** @var callable $callback */
                $callback(
                    $this->getViewBridgeName(),
                    $this->model->leafPath,
                    $viewBridges,
                    $allDeployedUrls);
            } else {
                $recursiveViewBridgerPrinter = function($viewBridgeClass, $leafPath, $childViewBridges, $recursiveViewBridgerPrinter){
                    $jsCode = "new window.rhubarb.viewBridgeClasses." . $viewBridgeClass . "( '" . $leafPath . "' ";
                    $childCodes = [];
                    foreach($childViewBridges as $childPath => $childViewBridgeDetails){
                        $childCodes[] = $recursiveViewBridgerPrinter(
                            $childViewBridgeDetails[0],
                            $childPath,
                            $childViewBridgeDetails[1],
                            $recursiveViewBridgerPrinter);
                    }
                    if (count($childCodes)){
                        $jsCode .= ", function(){\r\n".implode(";\r\n",$childCodes)."\r\n}";
                    }

                    $jsCode .=  ")";

                    return $jsCode;
                };

                $jsCode = $recursiveViewBridgerPrinter(
                    $this->getViewBridgeName(),
                    $this->model->leafPath,
                    $viewBridges,
                    $recursiveViewBridgerPrinter);

                $jsAndCssUrls = [];

                if ($resourcePackage != null){
                    $jsAndCssUrls = [];

                    foreach ($allDeployedUrls as $url) {
                        if (preg_match("/\.js$/", $url) || preg_match("/\.css$/", $url)) {
                            $jsAndCssUrls[] = $url;
                        }
                    }
                }

                ResourceLoader::addScriptCode($jsCode, array_unique($jsAndCssUrls));
            }

        }

        if (($resourcePackage != null) && (Application::current()->developerMode)){
            $resourcePackage->deploy();
        }

        return $content;
    }

    /**
     * Provides and extending View an opportunity to wrap the content with some additional HTML.
     *
     * @param string $content The original content
     * @return string The wrapped content.
     */
    protected function wrapViewContent($content)
    {
        return $content;
    }

    /**
     * Returns an array of resource URLs required by this View that don't need deployed.
     *
     * Normally these would be externally hosted scripts and css files.
     *
     * @return string[]
     */
    protected function getAdditionalResourceUrls()
    {
        return [];
    }

    /**
     * If the leaf requires a view bridge this returns it's name.
     *
     * @return string|bool
     */
    protected function getViewBridgeName()
    {
        if ($this->model->isRootLeaf){
            // If we're the root leaf, we're also the host for all client side view bridge events.
            // It's a real chore to have to define a view bridge just to allow child view bridges to
            // fire events.
            return "ViewBridge";
        }

        return false;
    }

    /**
     * @return string
     */
    private function getStateKey()
    {
        return $this->model->leafPath . "State";
    }

    /**
     * Gets the default layout provider and binds to the generateValueEvent event
     *
     * @return LayoutProvider
     */
    protected final function getLayoutProvider()
    {
        $layout = LayoutProvider::getProvider();
        $layout->generateValueEvent->attachHandler(function($elementName){
            if (isset($this->leaves[$elementName])){
                return $this->leaves[$elementName];
            }

            return null;
        });

        return $layout;
    }

    protected function layoutItemsWithContainer($containerTitle = "", ...$items)
    {
        $layout = $this->getLayoutProvider();
        $layout->printItemsWithContainer($containerTitle, ...$items);
    }

    protected function layoutItems($items = [])
    {
        $layout = $this->getLayoutProvider();
        $layout->printItems($items);
    }
}
