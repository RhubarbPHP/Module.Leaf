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

require_once __DIR__ . '/../../ControlView.php';

use Rhubarb\Leaf\Presenters\Controls\ControlModel;
use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\PresenterModel;

class TextBoxView extends ControlView
{
    /**
     * @var TextBoxModel
     */
    protected $model;

    public function __construct($htmlType = "text")
    {
        parent::__construct();

        $this->requiresContainer = false;
        $this->requiresStateInputs = false;
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/TextBoxViewBridge.js";

        return $package;
    }

    protected function getClientSideViewBridgeName()
    {
        return "TextBoxViewBridge";
    }

    public function printViewContent()
    {
        $maxLength = ($this->maxLength) ? "maxlength=\"" . $this->maxLength . "\"" : "";
        $autoCompleteAttribute = (!$this->model->allowBrowserAutoComplete) ? " autocomplete=\"off\"" : "";

        $placeholderText = $this->model->placeholderText ? ' placeholder="' . \htmlentities($this->model->placeholderText) . '"' : "";
        ?>
        <input type="<?= \htmlentities($this->model->inputHtmlType); ?>" size="<?= $this->size; ?>" <?= $maxLength; ?>
               name="<?= \htmlentities($this->getIndexedPresenterPath()); ?>" value="<?= \htmlentities($this->model->value); ?>"
               id="<?= \htmlentities($this->getIndexedPresenterPath()); ?>" presenter-name="<?= \htmlentities(
            $this->model->presenterName
        ); ?>"<?= $autoCompleteAttribute . $this->getHtmlAttributeTags() . $placeholderText . $this->getClassTag() ?> />
        <?php
    }

    private $size;

    public function setSize($size)
    {
        $this->size = $size;
    }

    private $maxLength;

    public function setMaxLength($length)
    {
        $this->maxLength = $length;
    }

    public function getSpawnSettings()
    {
        $settings = parent::getSpawnSettings();
        $settings["type"] = $this->model->htmlType;
        $settings["size"] = $this->model->size;
        $settings["maxLength"] = $this->model->maxLength;
        $settings["allowBrowserAutoComplete"] = $this->model->allowBrowserAutoComplete;

        return $settings;
    }
}
