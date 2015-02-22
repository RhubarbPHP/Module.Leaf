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

namespace Rhubarb\Leaf\Presenters\Application\Table\Columns;

use Rhubarb\Stem\Models\Model;

require_once __DIR__ . "/TableColumn.php";

class Template extends TableColumn
{
    public $template = "";

    public function __construct($template, $label = "")
    {
        parent::__construct($label);

        $this->template = $template;
    }

    /**
     * Implement this to return the content for a cell.
     *
     * @param \Rhubarb\Stem\Models\Model $row
     * @param \Rhubarb\Stem\Decorators\DataDecorator $decorator
     * @return mixed
     */
    public function getCellValue(Model $row, $decorator)
    {
        return \Rhubarb\Crown\String\Template::parseTemplate($this->template, ($decorator != null) ? $decorator : $row);
    }
}