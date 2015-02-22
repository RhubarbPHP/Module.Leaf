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

require_once __DIR__ . "/TableColumn.php";
require_once __DIR__ . "/SortableColumn.php";

use Rhubarb\Stem\Decorators\DataDecorator;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\DateTime;
use Rhubarb\Stem\Schema\Columns\Boolean;
use Rhubarb\Stem\Schema\Columns\Column;
use Rhubarb\Stem\Schema\Columns\Date;
use Rhubarb\Stem\Schema\Columns\Float;
use Rhubarb\Stem\Schema\Columns\Integer;
use Rhubarb\Stem\Schema\Columns\Money;
use Rhubarb\Stem\Schema\Columns\Time;

/**
 * A table column bound to a property of a model object.
 */
class ModelColumn extends TableColumn implements SortableColumn
{
    /**
     * @var Column
     */
    private $columnName;

    private $sortColumnName;

    public function __construct($columnName, $label = "", $sortColumnName = "")
    {
        $this->columnName = $columnName;
        $label = ($label == "") ? $columnName : $label;

        $this->sortColumnName = ($sortColumnName == "") ? $columnName : $sortColumnName;

        parent::__construct($label);
    }

    /**
     * Implement this to return the content for a cell.
     *
     * @param Model $row
     * @param DataDecorator $decorator
     * @return mixed
     */
    protected function getCellValue(Model $row, $decorator)
    {
        if ($decorator !== null) {
            return $decorator[$this->columnName];
        } else {
            return $row[$this->columnName];
        }
    }

    public function setSortableColumnName($columnName)
    {
        $this->sortColumnName = $columnName;
    }

    /**
     * Creates the correct type of table column for the supplied model column.
     *
     * @param \Rhubarb\Stem\Schema\Columns\Column $column
     * @param $label
     * @return DateColumn|ModelColumn
     */
    public static function createTableColumnForSchemaColumn(Column $column, $label)
    {
        if ($column instanceof Time) {
            new TimeColumn($column->columnName, $label);
        }

        if ($column instanceof Date || $column instanceof DateTime) {
            return new DateColumn($column->columnName, $label);
        }

        if ($column instanceof Boolean) {
            return new BooleanColumn($column->columnName, $label);
        }

        $tableColumn = new ModelColumn($column->columnName, $label);

        if ($column instanceof Integer) {
            $tableColumn->addCssClass("number");
            $tableColumn->addCssClass("integer");
        }

        if ($column instanceof Float) {
            $tableColumn->addCssClass("number");
            $tableColumn->addCssClass("float");
        }

        if ($column instanceof Money) {
            $tableColumn->addCssClass("money");
        }

        return $tableColumn;
    }

    public function getSortableColumnName()
    {
        return $this->sortColumnName;
    }
}