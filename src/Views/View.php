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
    protected $leaves;

    /**
     * Tracks the number of times a leaf name has occurred for sub leafs
     *
     * This is used to ensure if two leaves get added with the same name, they are differentiated by a numerical suffix.
     *
     * @var int[]
     */
    private $namesUsed = [];

    public final function __construct(LeafModel $model)
    {
        $this->model = $model;
        $this->createSubLeaves();
    }

    public function setWebRequest(WebRequest $request)
    {
        $this->request = $request;
        $this->restoreStateIntoModel();
    }

    private function restoreStateIntoModel()
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
     * The place where extending classes should create and register new Views
     */
    protected function createSubLeaves()
    {

    }

    protected function registerSubLeaf(Leaf $subLeaf)
    {
        $name = $subLeaf->getName();

        if (isset($this->namesUsed[$name])){
            $this->namesUsed[$name]++;
            $name .= $this->namesUsed[$name];
        } else {
            $this->namesUsed[$name] = 0;
        }

        $subLeaf->setName($name);
        $this->leaves[$name] = $subLeaf;

        if ($subLeaf instanceof BindableLeafInterface){
            // Setup data bindings
            $event = $subLeaf->getBindingValueChangedEvent();

            $event->attachHandler(function() use ($name, $subLeaf){
                $this->model->$name = $subLeaf->getBindingValue();
            });

            if (isset($this->model->$name)){
                $subLeaf->setBindingValue($this->model->$name);
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

    public function renderContent()
    {
        ob_start();

        $this->printViewContent();

        $content = ob_get_clean();

        $state = $this->model->getState();
        $state = json_encode($state);

        $content .= '
<input type="hidden" name="'.$this->getStateKey().'" value="'.htmlentities($state).'" />';

        return $content;
    }

    /**
     * @return string
     */
    private function getStateKey()
    {
        return $this->model->leafName . "_state";
    }
}
