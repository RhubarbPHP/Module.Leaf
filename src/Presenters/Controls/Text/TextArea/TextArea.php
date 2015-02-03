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

namespace Rhubarb\Leaf\Presenters\Controls\Text\TextArea;

require_once __DIR__ . "/../TextBox/TextBox.php";

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class TextArea extends TextBox
{
    public $rows;
    public $cols;

    public function __construct($name = "", $rows = 5, $cols = 40)
    {
        parent::__construct($name);

        $this->rows = $rows;
        $this->cols = $cols;
    }

    public function setRows($rows)
    {
        $this->rows = $rows;

        return $this;
    }

    public function setCols($cols)
    {
        $this->cols = $cols;

        return $this;
    }

    protected function createView()
    {
        return new TextAreaView();
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->rows = $this->rows;
        $this->view->cols = $this->cols;
    }
}