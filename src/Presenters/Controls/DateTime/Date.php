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

require_once __DIR__ . "/../Text/TextBox/TextBox.php";

use Rhubarb\Crown\DateTime\RhubarbDate;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class Date extends TextBox
{
    public function __construct($name = "", $defaultValue = null)
    {
        parent::__construct($name);

        $this->setSize(10);

        $this->attachClientSidePresenterBridge = true;

        $this->defaultValue = $defaultValue;
    }

    protected function applyModelToView()
    {
        if ($this->defaultValue !== null && $this->model->Text == "") {
            $this->model->Text = date("d/m/Y", strtotime($this->defaultValue));
        }

        parent::applyModelToView();
    }

    protected function applyBoundData($data)
    {
        $time = false;

        try {
            $time = new RhubarbDate($data);
        } catch (\Exception $er) {

        }

        if ($time === false) {
            $this->model->Text = "";
        } else {
            $this->model->Text = $time->format("d/m/Y");
        }
    }

    protected function extractBoundData()
    {
        if (preg_match('|(\d{1,2})/(\d{1,2})/(\d{2,4})|', $this->model->Text, $match)) {
            return $match[3] . "-" . $match[2] . "-" . $match[1];
        }

        return "";
    }

    protected function createView()
    {
        return new DateView();
    }
}