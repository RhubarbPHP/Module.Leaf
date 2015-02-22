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

namespace Rhubarb\Leaf\Presenters\Controls\Selection\DropDown;

require_once __DIR__ . "/../SelectionControlView.php";

use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlView;

class DropDownView extends SelectionControlView
{
    public function __construct()
    {
        $this->requiresContainer = false;
        $this->requiresStateInputs = false;
    }

    protected function getClientSideViewBridgeName()
    {
        return "DropDownViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/drop-down.js";

        return $package;
    }

    protected function printViewContent()
    {
        $name = $this->getIndexedPresenterPath();

        if ($this->supportsMultiple) {
            $name .= "[]";
        }

        ?>
    <select name="<?= \htmlentities($name); ?>" id="<?= \htmlentities($this->getIndexedPresenterPath()); ?>"
            presenter-name="<?= \htmlentities($this->presenterName); ?>"<?= $this->getHtmlAttributeTags(
    ) . $this->getClassTag() ?>>
        <?php
        foreach ($this->availableItems as $item) {
            $itemList = [$item];
            $isGroup = false;

            if (isset($item->Children)) {
                $isGroup = true;
                $itemList = $item->Children;

                print "<optgroup label=\"" . htmlentities($item->label) . "\">";
            }

            foreach ($itemList as $subItem) {
                $value = $subItem->value;
                $text = $subItem->label;

                $selected = ($this->isValueSelected($value)) ? " selected=\"selected\"" : "";

                $data = json_encode($subItem);

                print "<option value=\"" . \htmlentities($value) . "\"" . $selected . " data-item=\"" . htmlentities(
                        $data
                    ) . "\">" . \htmlentities($text) . "</option>
";
            }

            if ($isGroup) {
                print "</optgroup>";
            }
        }
        ?>
        </select><?php
    }
}