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
use Rhubarb\Crown\Deployment\DeploymentPackage;
use Rhubarb\Crown\Deployment\Deployable;
use Rhubarb\Crown\Events\Event;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Leaves\BindableLeafInterface;
use Rhubarb\Leaf\Leaves\Leaf;
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

    /**
     * @param Leaf[] ...$subLeaves
     */
    protected final function registerSubLeaf(...$subLeaves)
    {
        foreach($subLeaves as $subLeaf) {
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
                    $bindingValue = $subLeaf->getBindingValue();
                    if ($index !== null){
                        if (!isset($this->model->$name) || !is_array($this->model->$name)){
                            $this->model->$name = [];
                        }

                        $this->model->$name[$index] = $bindingValue;
                    } else {
                        $this->model->$name = $bindingValue;
                    }
                });
                
                $event = $subLeaf->getBindingValueRequestedEvent();
                $event->attachHandler(function($index = null) use ($name){
                    if ($index !== null ){
                        if (isset($this->model->$name[$index])){
                            return $this->model->$name[$index];
                        } else {
                            return null;
                        }
                    } else {
                        return isset($this->model->$name) ? $this->model->$name : null;
                    }
                });
            }
        }
    }

    /**
     * @return DeploymentPackage
     */
    public function getDeploymentPackage()
    {

    }

    protected function printViewContent()
    {

    }

    public final function renderContent()
    {
        ob_start();

        $this->beforeRenderEvent->raise();
        $this->printViewContent();

        $content = ob_get_clean();

        $state = $this->model->getState();
        $state = json_encode($state);

        if ($this->requiresStateInput) {
            $content .= '
<input type="hidden" name="' . $this->getStateKey() . '" value="' . htmlentities($state) . '" />';
        }

        if ($this->model->isRootLeaf){
            $content = '
<form method="post">
'.$content.'
</form>
';
        }

        return $content;
    }

    /**
     * @return string
     */
    private function getStateKey()
    {
        return $this->model->leafPath . "_state";
    }
}
