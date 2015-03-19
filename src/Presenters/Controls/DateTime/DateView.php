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

namespace Rhubarb\Leaf\Presenters\Controls\DateTime;

require_once __DIR__ . "/../Text/TextBox/TextBoxView.php";

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;

class DateView extends TextBoxView
{
    protected function getClientSideViewBridgeName()
    {
        return "DatePicker";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = "vendor/components/jquery/jquery.js";
        $package->resourcesToDeploy[] = "vendor/components/jqueryui/jqueryui.js";
        $package->resourcesToDeploy[] = "vendor/components/jqueryui/themes/base/jquery-ui.css";
        $package->resourcesToDeploy[] = __DIR__ . "/../../../../resources/jquery-presenter.js";
        $package->resourcesToDeploy[] = __DIR__ . "/date-picker.js";

        return $package;
    }

}