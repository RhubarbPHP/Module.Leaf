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

use Rhubarb\Crown\Context;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Presenters\SpawnableByViewBridgePresenter;

/**
 * Provides a base class for control presenters
 *
 * Adds data binding support to a hosting presenter's model.
 *
 * @property string $CssClassNames The names of the CSS classes to pass to our view.
 */
class ControlPresenter extends SpawnableByViewBridgePresenter
{
    protected $label = "";

    public function setLabel($labelText)
    {
        $this->label = $labelText;
    }

    public function addCssClassNames($classNames = [])
    {
        $classes = $this->CssClassNames;

        if (!is_array($classes)) {
            $classes = [];
        }

        $classes = array_merge($classes, $classNames);
        $this->CssClassNames = $classes;
    }

    public function addCssClassName($className)
    {
        $this->addCssClassNames([$className]);
    }

    public function addHtmlAttribute($attributeName, $attributeValue)
    {
        $attributes = $this->HtmlAttributes;

        if (!is_array($attributes)) {
            $attributes = [];
        }

        $attributes[$attributeName] = $attributeValue;

        $this->HtmlAttributes = $attributes;
    }

    protected function applyModelToView()
    {
        $this->view->cssClassNames = $this->CssClassNames;
        $this->view->htmlAttributes = $this->HtmlAttributes;

        parent::applyModelToView();
    }

    protected function applyBoundData($data)
    {
        $this->model->Value = $data;
    }

    protected function extractBoundData()
    {
        return $this->model->Value;
    }

    protected function parseRequestForCommand()
    {
        $request = Context::currentRequest();
        $values = $request->Post($this->getIndexedPresenterPath());

        if ($values !== null) {
            $this->model->Value = $values;
            $this->setBoundData();
        }
    }

    /**
     * Returns a label that the hosting view can use in the HTML output.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->label != "") {
            return $this->label;
        }

        return StringTools::wordifyStringByUpperCase($this->getName());
    }
}
