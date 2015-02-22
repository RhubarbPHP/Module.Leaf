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

namespace Rhubarb\Leaf\Presenters\Controls\CheckBoxes;

require_once __DIR__ . "/../ControlPresenter.php";

use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class CheckBox extends ControlPresenter
{
    protected function createView()
    {
        return new CheckBoxView();
    }

    protected function parseRequestForCommand()
    {
        if ($_SERVER["REQUEST_METHOD"] != "POST") {
            return;
        }

        $request = $request = \Rhubarb\Crown\Context::currentRequest();
        $values = $request->Post($this->getIndexedPresenterPath());

        if (is_array($values)) {
            foreach ($values as $index => $value) {
                $this->viewIndex = str_replace("_", "", $index);
                $this->model->Value = $value;
                $this->setBoundData();
            }
        } else {
            if ($values !== null) {
                $this->model->Value = $values;
                $this->setBoundData();
            } else {
                $this->model->Value = 0;
                $this->setBoundData();
            }
        }
    }

    protected function applyModelToView()
    {
        parent::applyModelToView();

        $this->view->setCheckedStatus((bool)$this->Value);
    }
}