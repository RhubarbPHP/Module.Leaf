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

require_once __DIR__ . '/../CompositeControlPresenter.php';

use Rhubarb\Crown\DateTime\RhubarbTime;
use Rhubarb\Leaf\Presenters\Controls\CompositeControlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;

class Time extends CompositeControlPresenter
{
    private $defaultValue = null;
    private $minuteInterval;
    private $hourStart;
    private $hourEnd;

    public function __construct($name = "", $defaultValue = null, $minuteInterval = 1, $hourStart = 0, $hourEnd = 23)
    {
        parent::__construct($name);

        $this->defaultValue = $defaultValue;
        $this->minuteInterval = $minuteInterval;
        $this->hourStart = $hourStart;
        $this->hourEnd = $hourEnd;
    }

    protected function applyModelToView()
    {
        if ($this->defaultValue !== null && $this->model->Hours == "" && $this->model->Minutes == "") {
            $this->model->Hours = $this->defaultValue->format("H");
            $this->model->Minutes = $this->defaultValue->format("i");
        }

        parent::applyModelToView();
    }

    protected function applyBoundData($data)
    {
        $time = false;

        try {
            $time = new RhubarbTime($data);
        } catch (\Exception $er) {
        }

        if ($time === false) {
            $this->model->Hours = "";
            $this->model->Minutes = "";
        } else {
            $this->model->Hours = $time->format("H");
            $this->model->Minutes = $time->format("i");
        }
    }

    protected function extractBoundData()
    {
        $hours = (int)$this->model->Hours;
        $minutes = (int)$this->model->Minutes;

        $time = new RhubarbTime();
        $time->setTime($hours, $minutes);

        return $time;
    }

    protected function createView()
    {
        return new TimeView($this->minuteInterval, $this->hourStart, $this->hourEnd);
    }
}