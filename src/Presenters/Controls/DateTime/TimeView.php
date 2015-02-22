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

require_once __DIR__ . '/../ControlView.php';

use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;

class TimeView extends ControlView
{
    private $minuteInterval = 1;
    private $hourStart;
    private $hourEnd;

    function __construct($minuteInterval = 1, $hourStart = 0, $hourEnd = 23)
    {
        $this->minuteInterval = $minuteInterval;
        $this->hourStart = $hourStart;
        $this->hourEnd = $hourEnd;
    }

    public function createPresenters()
    {
        $this->addPresenters(
            $hours = new DropDown("Hours"),
            $minutes = new DropDown("Minutes")
        );

        $hourRange = range($this->hourStart, $this->hourEnd);
        $minuteRange = range(0, 59, $this->minuteInterval);

        $pad = function (&$value) {
            if ($value < 10) {
                $value = "0" . $value;
            }
        };

        array_walk($hourRange, $pad);
        array_walk($minuteRange, $pad);

        $hours->setSelectionItems($hourRange);
        $minutes->setSelectionItems($minuteRange);
    }

    public function printViewContent()
    {
        print $this->presenters["Hours"] . " " . $this->presenters["Minutes"];
    }

    protected function getClientSideViewBridgeName()
    {
        return "TimeViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/TimeViewBridge.js";

        return $package;
    }
}