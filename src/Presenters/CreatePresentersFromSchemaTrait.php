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
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\Decimal;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\Enum;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\MediumText;
use Rhubarb\Stem\Schema\Columns\Boolean;
use Rhubarb\Stem\Schema\Columns\Date;
use Rhubarb\Stem\Schema\Columns\DateTime;
use Rhubarb\Stem\Schema\Columns\Integer;
use Rhubarb\Stem\Schema\Columns\Money;
use Rhubarb\Stem\Schema\Columns\String;
use Rhubarb\Stem\Schema\Columns\Time;
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
        if ($column instanceof Boolean) {
            return new CheckBox($presenterName);
        }

        // Date
        if ($column instanceof Date || $column instanceof DateTime) {
            return new \Rhubarb\Leaf\Presenters\Controls\DateTime\Date($presenterName);
        }

        // Time
        if ($column instanceof Time) {
            $textBox = new \Rhubarb\Leaf\Presenters\Controls\DateTime\Time($presenterName);
            return $textBox;
        }

        // Drop Downs
        if ($column instanceof Enum) {
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
        if ($column instanceof MediumText) {
            $textArea = new TextArea($presenterName, 5, 40);

            return $textArea;
        }

        // TextBoxes
        if ($column instanceof String) {
            if (stripos($presenterName, "password") !== false) {
                return new Password($presenterName);
            }

            $textBox = new TextBox($presenterName);
            $textBox->setMaxLength($column->stringLength);

            return $textBox;
        }

        // Decimal
        if ($column instanceof Decimal || $column instanceof Money) {
            $textBox = new NumericTextBox($presenterName, 5);

            return $textBox;
        }

        // Int
        if ($column instanceof Integer) {
            $textBox = new TextBox($presenterName);
            $textBox->setSize(5);

            return $textBox;
        }

        return parent::createPresenterByName($presenterName);
    }
} 