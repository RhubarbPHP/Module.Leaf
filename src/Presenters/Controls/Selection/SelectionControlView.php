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

namespace Rhubarb\Leaf\Presenters\Controls\Selection;

require_once __DIR__ . "/../JQueryControlView.php";

use Rhubarb\Leaf\Presenters\Controls\JQueryControlView;

class SelectionControlView extends JQueryControlView
{
    protected $availableItems = [];

    public function setAvailableItems($items = [])
    {
        $this->availableItems = $items;
    }

    protected $selectedItems = [];

    public function setSelectedItems($values = [])
    {
        $this->selectedItems = $values;
    }

    protected $supportsMultiple = false;

    public function setSupportsMultiple($value)
    {
        $this->supportsMultiple = $value;
    }

    public function getSpawnSettings()
    {
        $settings = parent::getSpawnSettings();
        $settings["AvailableItems"] = $this->availableItems;

        return $settings;
    }

    protected function getClientSideViewBridgeName()
    {
        return "SelectionControlViewBridge";
    }

    protected function isValueSelected($value)
    {
        foreach ($this->selectedItems as $item) {
            if ($item->value == $value) {
                return true;
            }
        }

        return false;
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/SelectionControlViewBridge.js";

        return $package;
    }
}