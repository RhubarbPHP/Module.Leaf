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

namespace Rhubarb\Leaf\Presenters\Controls\Text\NumericTextBox;

require_once __DIR__ . '/../TextBox/TextBox.php';

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class NumericTextBox extends TextBox
{
    private $decimalPlaces = 2;

    public function __construct($name = "", $size = 15)
    {
        parent::__construct($name, $size);
    }

    protected function extractBoundData()
    {
        return $this->model->Text;
    }

    protected function applyModelToView()
    {
        parent::applyModelToView();

        $number = (float)$this->model->Text;

        $this->view->setText(number_format($number, $this->decimalPlaces, '.', ''));
    }
}