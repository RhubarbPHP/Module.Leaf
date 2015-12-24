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

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Presenters\Controls\CheckBoxes\CheckBox;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;
use Rhubarb\Leaf\Presenters\Controls\Text\NumericTextBox\NumericTextBox;
use Rhubarb\Leaf\Presenters\Controls\Text\Password\Password;
use Rhubarb\Leaf\Presenters\Controls\Text\TextArea\TextArea;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\MySqlEnumColumn;
use Rhubarb\Stem\Schema\Columns\BooleanColumn;
use Rhubarb\Stem\Schema\Columns\DateColumn;
use Rhubarb\Stem\Schema\Columns\DateTimeColumn;
use Rhubarb\Stem\Schema\Columns\DecimalColumn;
use Rhubarb\Stem\Schema\Columns\IntegerColumn;
use Rhubarb\Stem\Schema\Columns\LongStringColumn;
use Rhubarb\Stem\Schema\Columns\MoneyColumn;
use Rhubarb\Stem\Schema\Columns\StringColumn;
use Rhubarb\Stem\Schema\Columns\TimeColumn;
use Rhubarb\Stem\Schema\SolutionSchema;

trait CreatePresentersFromSchemaTrait
{
    protected function createPresenterByName($presenterName)
    {
        $restModel = $this->getRestModel();

        if ($restModel) {
            $class = $restModel->getModelName();
            $schema = $restModel->getSchema();
        } else {
            $restCollection = $this->getRestCollection();

            $class = $restCollection->getModelClassName();
            $schema = $restCollection->getModelSchema();
        }

        // See if the model has a relationship with this name.
        $relationships = SolutionSchema::getAllOneToOneRelationshipsForModelBySourceColumnName($class);

        $columnRelationships = false;

        if (isset($relationships[$presenterName])) {
            $columnRelationships = $relationships[$presenterName];
        } else {
            if ($presenterName == $schema->uniqueIdentifierColumnName) {
                if (isset($relationships[""])) {
                    $columnRelationships = $relationships[""];
                }
            }
        }

        if ($columnRelationships) {
            $relationship = $relationships[$presenterName];

            $collection = $relationship->getCollection();

            $dropDown = new DropDown($presenterName, "");
            $dropDown->setSelectionItems(
                [
                    ["", "Please Select"],
                    $collection
                ]
            );

            $dropDown->setLabel(StringTools::wordifyStringByUpperCase($relationship->getNavigationPropertyName()));

            return $dropDown;
        }

        $columns = $schema->getColumns();

        if (!isset($columns[$presenterName])) {
            return null;
        }

        $column = $columns[$presenterName];

        // Checkbox
        if ($column instanceof BooleanColumn) {
            return new CheckBox($presenterName);
        }

        // Date
        if ($column instanceof DateColumn || $column instanceof DateTimeColumn) {
            return new \Rhubarb\Leaf\Presenters\Controls\DateTime\Date($presenterName);
        }

        // Time
        if ($column instanceof TimeColumn) {
            $textBox = new \Rhubarb\Leaf\Presenters\Controls\DateTime\Time($presenterName);
            return $textBox;
        }

        // Drop Downs
        if ($column instanceof MySqlEnumColumn) {
            $dropDown = new DropDown($presenterName, $column->defaultValue);
            $dropDown->setSelectionItems(
                [
                    ["", "Please Select"],
                    $column
                ]
            );

            return $dropDown;
        }

        // TextArea
        if ($column instanceof LongStringColumn) {
            $textArea = new TextArea($presenterName, 5, 40);

            return $textArea;
        }

        // TextBoxes
        if ($column instanceof StringColumn) {
            if (stripos($presenterName, "password") !== false) {
                return new Password($presenterName);
            }

            $textBox = new TextBox($presenterName);
            $textBox->setMaxLength($column->maximumLength);

            return $textBox;
        }

        // Decimal
        if ($column instanceof DecimalColumn || $column instanceof MoneyColumn) {
            $textBox = new NumericTextBox($presenterName, 5);

            return $textBox;
        }

        // Int
        if ($column instanceof IntegerColumn) {
            $textBox = new TextBox($presenterName);
            $textBox->setSize(5);

            return $textBox;
        }

        return parent::createPresenterByName($presenterName);
    }
}