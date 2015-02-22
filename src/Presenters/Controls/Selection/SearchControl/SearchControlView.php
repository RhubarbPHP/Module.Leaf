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

namespace Rhubarb\Leaf\Presenters\Controls\Selection\SearchControl;

require_once __DIR__ . "/../SelectionControlView.php";

use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlView;

class SearchControlView extends SelectionControlView
{
    public function printViewContent()
    {
        print '<input type="hidden" name="' . $this->getIndexedPresenterPath() . '" />';
    }

    protected function getClientSideViewBridgeName()
    {
        return "SearchControl";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/search-control.js";
        $package->resourcesToDeploy[] = __DIR__ . "/SearchControl.css";
        $package->resourcesToDeploy[] = __DIR__ . "/Resources/ajax-loader.gif";

        return $package;
    }
}