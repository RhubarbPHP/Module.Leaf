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

/**
 * Stores information about a column used in a Table
 */
abstract class TableColumn
{
    public $label = "";

    /**
     * Whether or not to allow sorting.
     *
     * While all columns are now sortable, it is not necessarily wise to do so on computed columns or
     * large text columns in a large data set.
     *
     * @var bool
     */
    public $sortable = true;

    private $cssClasses = [];

    public function __construct($label = "")
    {
        $this->label = $label;
    }

    public function addCssClass($className)
    {
        $this->cssClasses[] = $className;

        return $this;
    }

    public function getCssClasses()
    {
        return $this->cssClasses;
    }

    protected function getFormattedValue($value)
    {
        return $value;
    }

    public final function getCellContent(Model $row, $decorator)
    {
        $value = $this->getCellValue($row, $decorator);

        return $this->getFormattedValue($value);
    }

    /**
     * Implement this to return the content for a cell.
     *
     * @param \Rhubarb\Stem\Models\Model $row
     * @param \Rhubarb\Stem\Decorators\DataDecorator $decorator
     * @return mixed
     */
    protected abstract function getCellValue(Model $row, $decorator);

    /**
     * Returns an array of custom cell attributes to use on the <td>
     *
     * @param \Rhubarb\Stem\Models\Model $row
     * @return array
     */
    public function getCustomCellAttributes(Model $row)
    {
        return [];
    }
}