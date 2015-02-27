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

namespace Rhubarb\Leaf\Presenters\Controls\Selection;

require_once __DIR__ . "/../ControlPresenter.php";

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\Enum;

/**
 * A base class for all controls implementing a range of options to pick from.
 *
 * Provides support for manual items, items from an enum column type, from a model collection
 * and callbacks.
 */
class SelectionControlPresenter extends ControlPresenter
{
    protected function initialiseModel()
    {
        parent::initialiseModel();

        if (!isset($this->model->SelectedItems)) {
            $this->model->SelectedItems = [];
        }
    }

    protected $selectionItems = [];

    public function getSelectionItems()
    {
        return $this->selectionItems;
    }

    public function setSelectionItems(array $items)
    {
        $this->selectionItems = $items;

        return $this;
    }

    protected function supportsMultipleSelection()
    {
        return false;
    }

    /**
     * Override this function to get a label for a selected item.
     *
     * This is normally only called for the initial render of the page as during searching the labels are already
     * available. Also there is no sensible default implementation for this function as the meaning of $item
     * is known only to the overriding class.
     *
     * @param $item
     * @return string
     */
    protected function getLabelForItem($item)
    {
        return "";
    }

    protected function getPublicModelPropertyList()
    {
        $properties = parent::getPublicModelPropertyList();
        $properties[] = "SelectedItems";

        return $properties;
    }

    protected function applyModelToView()
    {
        parent::applyModelToView();

        $this->view->setAvailableItems($this->getCurrentlyAvailableSelectionItems());
        $this->view->setSelectedItems($this->model->SelectedItems);
        $this->view->setSupportsMultiple($this->supportsMultipleSelection());
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->attachEventHandler(
            "UpdateAvailableSelectionItems",
            function () {
                $args = func_get_args();

                call_user_func_array(array($this, "updateAvailableSelectionItems"), $args);

                return $this->getCurrentlyAvailableSelectionItems();
            }
        );
    }

    protected function updateAvailableSelectionItems($itemId)
    {

    }

    protected function parseRequestForCommand()
    {
        $request = Context::currentRequest();
        $values = $request->Post($this->getIndexedPresenterPath());

        if ($values !== null) {
            if (!is_array($values)) {
                $values = explode(",", $values);
            }

            $this->setSelectedItems($values);
            $this->setBoundData();
        }
    }

    /**
     * Override this function to get the data associated with a selected model item.
     *
     * By default this will use all the public data however for efficiency you can return a specific
     * array of values.
     *
     * @param $item
     * @return Array
     */
    protected function getDataForItem($item)
    {
        if ($item instanceof Model) {
            return $item->exportPublicData();
        }

        return [];
    }

    protected function isValueSelectable($value)
    {
        if ($value === null) {
            return false;
        }

        return true;
    }

    /**
     * If your selection control presenter works with models, this function should return
     * the appropriate model for a selected value.
     *
     * @param $value
     */
    protected function convertValueToModel($value)
    {
        return $value;
    }

    public function setSelectedItems($rawItems)
    {
        if (is_object($rawItems)) {
            if ($rawItems instanceof Model) {
                $rawItems = [$rawItems->UniqueIdentifier];
            }
        } else {
            if (is_int($rawItems) || is_bool($rawItems)) {
                $rawItems = [$rawItems];
            } elseif (!is_array($rawItems)) {
                $rawItems = explode(",", $rawItems);
            }
        }

        $selectedItems = [];

        foreach ($rawItems as $value) {
            if (!$this->isValueSelectable($value)) {
                continue;
            }

            if ($value === 0 || $value === "0") {
                $item = $this->makeItem($value, "", []);
            } else {
                if (!$value instanceof Model) {
                    $value = $this->convertValueToModel($value);
                }

                $optionValue = ($value instanceof Model) ? $value->UniqueIdentifier : $value;

                $item = $this->makeItem($optionValue, $this->getLabelForItem($value), $this->getDataForItem($value));
            }

            $selectedItems[] = $item;
        }

        $this->model->SelectedItems = $selectedItems;
    }

    protected function applyBoundData($data)
    {
        $this->setSelectedItems($data);
    }

    private function buildDataArrayFromSelectedList($list)
    {
        $data = [];

        foreach ($list as $key => $value) {
            if (is_object($value)) {
                $value = $value->value;
            } else {
                $value = $value["value"];
            }

            $data[$key] = $value;
        }

        return $data;
    }

    protected function extractBoundData()
    {
        // We have to decide how to return the list of selected items.
        // If there is only one selected item we will just return that item, however if there
        // are many items we will return the full array. We'll assume that which ever presenter
        // is processing the bound value will handle detection of the two scenarios.
        // Returning a single value is important to simplify occasions where a selection control is
        // directly bound to a single value column.

        $data = $this->buildDataArrayFromSelectedList($this->model->SelectedItems);

        if ($this->supportsMultipleSelection()) {
            return $data;
        }

        if (sizeof($data) > 0) {
            return current($data);
        } else {
            return "";
        }
    }

    /**
     * Makes a stdClass to represent an item.
     *
     * This will make a standard object with the following properties:
     *
     * Value: The value of the item
     * Label: A text display value for the item
     * Data: Any other associated item data
     *
     * Note that these properties are UpperCamelCase as these objects are often converted directly into
     * Javascript objects and that best matches our current javascript styles.
     *
     * @param $value
     * @param $label
     * @param $data
     * @return \stdClass
     */
    protected final function makeItem($value, $label, $data = [])
    {
        $item = new \stdClass();
        $item->value = $value;
        $item->label = $label;
        $item->data = $data;

        return $item;
    }

    /**
     * Returns an array of all the items that should be available for selection.
     *
     * @return array
     */
    protected function getCurrentlyAvailableSelectionItems()
    {
        $totalItems = [];
        $selectionItems = $this->getSelectionItems();

        foreach ($selectionItems as $group => $item) {
            $items = [];

            if ($item instanceof Collection) {
                foreach ($item as $key => $model) {
                    $items[] = $this->makeItem($key, $model->GetLabel(), $this->getDataForItem($model));
                }
            } elseif ($item instanceof Enum) {
                $enumValues = $item->enumValues;

                foreach ($enumValues as $enumValue) {
                    $items[] = $this->makeItem($enumValue, $enumValue);
                }
            } elseif (is_array($item)) {
                if (is_array($item[0])) {
                    foreach ($item as $subItem) {
                        $value = $subItem[0];
                        $label = (sizeof($subItem) == 1) ? $subItem[0] : $subItem[1];

                        $data = (sizeof($subItem) > 2) ? $subItem[2] : [];

                        $items[] = $this->makeItem($value, $label, $data);
                    }
                } else {
                    $value = $item[0];
                    $label = (sizeof($item) == 1) ? $item[0] : $item[1];

                    $data = (sizeof($item) > 2) ? $item[2] : [];

                    $items[] = $this->makeItem($value, $label, $data);
                }
            } else {
                $items[] = $this->makeItem($item, $item);
            }

            if (is_numeric($group)) {
                $totalItems = array_merge($totalItems, $items);
            } else {
                $groupItem = $this->makeItem("", $group);
                $groupItem->Children = $items;

                $totalItems[] = $groupItem;
            }
        }

        return $totalItems;
    }
}