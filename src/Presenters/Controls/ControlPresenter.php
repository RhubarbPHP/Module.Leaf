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

namespace Rhubarb\Leaf\Presenters\Controls;

require_once __DIR__ . "/../SpawnableByViewBridgePresenter.php";

use Rhubarb\Crown\Request\Request;
use Rhubarb\Leaf\Presenters\BindablePresenterInterface;
use Rhubarb\Leaf\Presenters\BindablePresenterTrait;
use Rhubarb\Leaf\Presenters\SpawnableByViewBridgePresenter;

/**
 * Provides a base class for control presenters
 *
 * Adds data binding support to a hosting presenter's model.
 *
 */
class ControlPresenter extends SpawnableByViewBridgePresenter implements BindablePresenterInterface
{
    use BindablePresenterTrait;

    /**
     * @var ControlModel
     */
    public $model;

    public function setLabel($labelText)
    {
        $this->model->label = $labelText;
    }

    public function addCssClassNames($classNames = [])
    {
        $this->model->addCssClassNames($classNames);
    }

    public function addCssClassName($className)
    {
        $this->model->addCssClassName($className);
    }

    public function addHtmlAttribute($attributeName, $attributeValue)
    {
        $this->model->addHtmlAttribute($attributeName, $attributeValue);
    }

    protected function parseRequestForCommand()
    {
        $request = Request::current();
        $values = $request->post($this->model->indexedPresenterPath);

        if ($values !== null) {
            $this->model->value = $values;
            $this->bindingValueChangedEvent->raise();
        }
    }

    /**
     * The overriding class should implement to return a model class that extends PresenterModel
     *
     * This is normally done with an anonymous class for convenience
     *
     * @return PresenterModel
     */
    protected function createModel()
    {
        return new ControlModel();
    }

    public function getBindingValue()
    {
        return $this->model->value;
    }

    public function setBindingValue($bindingValue)
    {
        return $this->model->value = $bindingValue;
    }
}
