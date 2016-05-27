<?php

/**
 * Copyright (c) 2016 RhubarbPHP.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Rhubarb\Leaf\Leaves\Controls;

abstract class CompositeControl extends Control
{
    /**
     * @var CompositeControlModel
     */
    protected $model;

    protected function onModelCreated()
    {
        parent::onModelCreated();

        $this->model->childControlValueChangedEvent->attachHandler(function($property, $value){
            $this->model->value = $this->createCompositeValue();
            $this->model->valueChangedEvent->raise();
        });
    }

    protected function createModel()
    {
        return new CompositeControlModel();
    }

    public function setValue($bindingValue)
    {
        parent::setValue($bindingValue);

        $this->parseCompositeValue($bindingValue);
    }

    /**
     * The place to parse the value property and break into the sub values for sub controls to bind to
     *
     * @param $compositeValue
     */
    protected abstract function parseCompositeValue($compositeValue);

    /**
     * The place to combine the model properties for sub values into a single value, array or object.
     *
     * @return mixed
     */
    protected abstract function createCompositeValue();
}