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

namespace Rhubarb\Leaf\Presenters\Controls\Text\TextBox;

require_once __DIR__ . "/../../ControlPresenter.php";

use Rhubarb\Crown\Request\Request;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

/**
 * @property TextBoxView $view
 */
class TextBox extends ControlPresenter
{
    /**
     * @var TextBoxModel
     */
    public $model;

    public function __construct($name = "", $size = 40, $inputHtmlType = 'text')
    {
        parent::__construct($name);

        $this->model->size = $size;
        $this->model->inputHtmlType = $inputHtmlType;
    }

    protected function createModel()
    {
        return new TextBoxModel();
    }

    /**
     * @param string $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->model->defaultValue = $defaultValue;

        if (!$this->model->value) {
            $this->model->value = $this->defaultValue;
        }
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->model->defaultValue;
    }

    /**
     * @param string $placeholderText
     */
    public function setPlaceholderText($placeholderText)
    {
        $this->model->placeholderText = $placeholderText;
    }

    /**
     * @return string
     */
    public function getPlaceholderText()
    {
        return $this->model->placeholderText;
    }

    protected function createView()
    {
        return new TextBoxView();
    }

    public function setSize($size)
    {
        $this->model->size = $size;

        return $this;
    }

    public function setMaxLength($length)
    {
        $this->model->maxLength = $length;

        return $this;
    }

    public function setAllowBrowserAutoComplete($allowBrowserAutoComplete)
    {
        $this->model->allowBrowserAutoComplete = $allowBrowserAutoComplete;
    }
}
