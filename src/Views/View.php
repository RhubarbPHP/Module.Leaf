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

use Rhubarb\Crown\Deployment\DeploymentPackage;
use Rhubarb\Crown\Deployment\Deployable;
use Rhubarb\Crown\Events\Event;
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

    public final function __construct(LeafModel $model)
    {
        $this->model = $model;
        $this->restoreStateIntoModel();

        $this->createSubLeaves();
    }

    private function restoreStateIntoModel()
    {
        $stateKey = $this->model->leafName."_state";

    }

    /**
     * The place where extending classes should create and register new Views
     */
    protected function createSubLeaves()
    {

    }

    protected function registerSubLeaf(Leaf $subLeaf)
    {
        if ($subLeaf instanceof BindableLeafInterface){
            // Setup data bindings
            $event = $subLeaf->getBindingValueChangedEvent();
            $name = $subLeaf->getName();

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

        return $content;
    }
}
