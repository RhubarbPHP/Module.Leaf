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

namespace Rhubarb\Leaf\Presenters\Controls\CheckBoxes;

require_once __DIR__ . "/../ControlView.php";
require_once __DIR__ . "/../../../Views/WithViewBridgeTrait.php";
require_once __DIR__ . "/../../../Views/SpawnableByViewBridgeViewTrait.php";

use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Views\SpawnableByViewBridgeViewTrait;
use Rhubarb\Leaf\Views\WithViewBridgeTrait;

class CheckBoxView extends ControlView
{
    use WithViewBridgeTrait;
    use SpawnableByViewBridgeViewTrait;

    private $checked = false;

    public function __construct()
    {
        $this->requiresContainer = false;
        $this->requiresStateInputs = false;
    }

    public function setCheckedStatus($checked)
    {
        $this->checked = $checked;
    }

    public function printViewContent()
    {
        $checked = ($this->checked) ? " checked=\"checked\"" : "";

        ?>
        <input type="checkbox" value="1" name="<?= \htmlentities($this->getIndexedPresenterPath()); ?>"
               id="<?= \htmlentities($this->getIndexedPresenterPath()); ?>"<?= $checked; ?>
               presenter-name="<?= \htmlentities($this->presenterName); ?>"<?= $this->getHtmlAttributeTags(
        ) . $this->getClassTag() ?> />
    <?php
    }

    /**
     * Implement this and return __DIR__ when your ViewBridge.js is in the same folder as your class
     *
     * @returns string Path to your ViewBridge.js file
     */
    public function getDeploymentPackageDirectory()
    {
        return __DIR__;
    }

    public function getSpawnSettings()
    {
        $settings = parent::getSpawnSettings();
        $settings["Checked"] = $this->checked;
        $settings["Attributes"] = $this->htmlAttributes;

        return $settings;
    }
}