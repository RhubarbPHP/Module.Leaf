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

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

require_once __DIR__ . "/../ControlView.php";

use Rhubarb\Leaf\Presenters\Controls\ControlView;

class SimpleHtmlFileUploadView extends ControlView
{
    public $filters = [];

    public function __construct()
    {
        $this->requiresContainer = false;
        $this->requiresStateInputs = false;
    }

    protected function printViewContent()
    {
        $this->printUploadInput();
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/SimpleHtmlFileUploadViewBridge.js";

        return $package;
    }

    protected function getClientSideViewBridgeName()
    {
        return "SimpleHtmlFileUploadViewBridge";
    }

    /**
     * Prints the upload input itself.
     *
     * Extending view classes should call this at some point in their PrintViewContent() method.
     */
    protected function printUploadInput()
    {
        $accepts = "";

        if (sizeof($this->filters) > 0) {
            $accepts = " accept=\"" . implode(",", $this->filters) . "\"";
        }

        ?>
        <input type="file" name="<?= $this->getIndexedPresenterPath(); ?>" id="<?= $this->getIndexedPresenterPath(); ?>"
               presenter-name="<?= $this->presenterName ?>"<?= $accepts . $this->getHtmlAttributeTags(
        ) . $this->getClassTag() ?>/>
    <?php
    }
}