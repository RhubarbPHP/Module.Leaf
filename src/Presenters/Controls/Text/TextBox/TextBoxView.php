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

use Rhubarb\Leaf\Presenters\Controls\ControlView;

class TextBoxView extends ControlView
{
    protected $htmlType = "text";

    protected $placeholderText = "";

    protected $allowBrowserAutoComplete = true;

    public function __construct($htmlType = "text")
    {
        $this->htmlType = $htmlType;

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

    protected $text = "";

    public function setText($text)
    {
        $this->text = $text;
    }

    public function setAllowBrowserAutoComplete($allowBrowserAutoComplete)
    {
        $this->allowBrowserAutoComplete = $allowBrowserAutoComplete;
    }

    /**
     * @param string $placeholderText
     */
    public function setPlaceholderText($placeholderText)
    {
        $this->placeholderText = $placeholderText;
    }

    public function printViewContent()
    {
        $maxLength = ($this->maxLength) ? "maxlength=\"" . $this->maxLength . "\"" : "";
        $autoCompleteAttribute = (!$this->allowBrowserAutoComplete) ? " autocomplete=\"off\"" : "";

        $placeholderText = $this->placeholderText ? ' placeholder="' . \htmlentities($this->placeholderText) . '"' : "";
        ?>
        <input type="<?= \htmlentities($this->htmlType); ?>" size="<?= $this->size; ?>" <?= $maxLength; ?>
               name="<?= \htmlentities($this->getIndexedPresenterPath()); ?>" value="<?= \htmlentities($this->text); ?>"
               id="<?= \htmlentities($this->getIndexedPresenterPath()); ?>" presenter-name="<?= \htmlentities(
            $this->presenterName
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
        $settings["size"] = $this->size;
        $settings["maxLength"] = $this->maxLength;
        $settings["allowBrowserAutoComplete"] = $this->allowBrowserAutoComplete;

        return $settings;
    }
}
