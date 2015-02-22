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

namespace Rhubarb\Leaf\Presenters\Application\Tabs;

require_once __DIR__ . '/../../../Views/JQueryView.php';

use Rhubarb\Leaf\Views\JQueryView;

class TabsView extends JQueryView
{
    /**
     * @var TabDefinition[]
     */
    protected $tabs;

    public function setTabDefinitions($tabs)
    {
        $this->tabs = $tabs;
    }

    protected function getClientSideViewBridgeName()
    {
        return "Tabs";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/TabsPresenter.js";

        return $package;
    }

    protected function printTab($tab)
    {
        $selected = ($tab->selected) ? " class=\"-is-selected\"" : "";
        print "<li{$selected}><a href='#'>" . $tab->label . "</a></li>";
    }

    public function printViewContent()
    {
        print "<ul class='tabs'>";

        foreach ($this->tabs as $tab) {
            $this->printTab($tab);
        }

        print "</ul>";
    }
}