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

require_once __DIR__ . '/SimpleHtmlFileUploadView.php';

class MultipleHtmlFileUploadView extends SimpleHtmlFileUploadView
{
    public function __construct()
    {
        parent::__construct();

        $this->requiresContainer = true;
        $this->requiresStateInputs = true;
    }

    protected function getClientSideViewBridgeName()
    {
        return "MultipleHtmlFileUploadViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/MultipleHtmlFileUploadViewBridge.js";

        return $package;
    }

    protected function printUploadInput()
    {
        $accepts = "";

        if (sizeof($this->filters) > 0) {
            $accepts = " accept=\"" . implode(",", $this->filters) . "\"";
        }

        ?>
        <input type="file" name="<?= $this->getIndexedPresenterPath(); ?>[]"
               id="<?= $this->getIndexedPresenterPath(); ?>"
               presenter-name="<?= $this->presenterName ?>" <?= $accepts; ?> multiple="multiple"/>
    <?php
    }
} 