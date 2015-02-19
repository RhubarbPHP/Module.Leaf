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

require_once __DIR__ . "/../TextBox/TextBoxView.php";

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;

class TextAreaView extends TextBoxView
{
    public $rows = 5;
    public $cols = 40;

    public function printViewContent()
    {
        $placeholderText = $this->placeholderText ? ' placeholder="' . \htmlentities($this->placeholderText) . '"' : "";

        ?>
        <textarea rows="<?= $this->rows; ?>" cols="<?= $this->cols; ?>"
                  name="<?= \htmlentities($this->presenterPath); ?>" id="<?= \htmlentities($this->presenterPath); ?>"
                  presenter-name="<?= \htmlentities($this->presenterName); ?>"<?= $this->getHtmlAttributeTags(
        ) . $this->getClassTag().$placeholderText ?>><?= $this->text; ?></textarea>
    <?php
    }
}