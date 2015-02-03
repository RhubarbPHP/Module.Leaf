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

namespace Rhubarb\Leaf\Presenters\Application\Search;

require_once __DIR__ . "/../../HtmlPresenter.php";

use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Stem\Filters\Filter;
use Rhubarb\Stem\Filters\Group;

class SearchPanel extends HtmlPresenter
{
    use ModelProvider;

    private $defaultControlValues = [];

    private $searchControlsColumnCount = 6;

    public function __construct($name = "")
    {
        parent::__construct($name);

        $this->snapshotDefaultControlValues();
    }

    protected function initialiseModel()
    {
        parent::initialiseModel();

        if (!isset($this->model->AutoSubmit)) {
            $this->model->AutoSubmit = false;
        }
    }

    protected function getPublicModelPropertyList()
    {
        $list = parent::getPublicModelPropertyList();
        $list[] = "AutoSubmit";

        return $list;
    }

    protected final function snapshotDefaultControlValues()
    {
        $controls = $this->getSearchControls();
        $defaultValues = $this->getDefaultControlValues();

        foreach ($controls as $control) {
            if (!isset($defaultValues[$control->GetName()])) {
                $defaultValues[$control->GetName()] = "";
            }
        }

        $this->model->mergeRawData($defaultValues);
        $this->defaultControlValues = $this->getSearchControlValues();
    }

    /**
     * Override to set default control values on the model.
     */
    protected function getDefaultControlValues()
    {
        return [];
    }

    protected function createView()
    {
        return new SearchPanelView();
    }

    private $searchControls = null;

    /**
     * You should implement this to return an ordered collection of control presenters to use in the search.
     *
     * @return array
     */
    protected function createSearchControls()
    {
        return [];
    }

    protected function setSearchControlsColumnCount($columns = 6)
    {
        $this->searchControlsColumnCount = $columns;
    }

    protected final function getSearchControls()
    {
        if ($this->searchControls === null) {
            $this->searchControls = $this->createSearchControls();
        }

        return $this->searchControls;
    }

    protected function applyModelToView()
    {
        parent::applyModelToView();

        $controls = $this->getSearchControls();

        $this->view->setSearchControlsColumnCount($this->searchControlsColumnCount);
    }

    /**
     * Returns a key value pair array of each control name and it's value
     *
     * @return Array
     */
    public function getSearchControlValues()
    {
        $data = $this->model->exportRawData();
        $controlData = [];

        foreach ($this->getSearchControls() as $control) {
            $controlName = $control->getName();

            if (isset($data[$controlName])) {
                $controlData[$controlName] = $data[$controlName];
            }
        }

        return $controlData;
    }

    /**
     * Sets the values of the search controls.
     *
     * @param array $controlValues A key value pair array.
     */
    public function setSearchControlValues($controlValues = [])
    {
        $controlValues = array_merge($this->defaultControlValues, $controlValues);

        foreach ($this->getSearchControls() as $control) {
            $controlName = $control->getName();

            if (isset($controlValues[$controlName])) {
                $this->model[$controlName] = $controlValues[$controlName];
            }
        }

        $this->rePresent();
        $this->raiseEvent("Search");
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->attachEventHandler("GetControls", function () {
            return $this->getSearchControls();
        });

        $this->view->attachEventHandler("Search", function () {
            $this->raiseEvent("Search");
        });
    }

    protected function bindEvents(Presenter $presenter)
    {
        $presenter->attachEventHandler("ConfigureFilters", array($this, "OnConfigureFilters"));
        $presenter->attachEventHandler("GetSearchControlValues", array($this, "GetSearchControlValues"));
    }

    /**
     * Override this method to create any filters that are required.
     *
     * @param \Rhubarb\Stem\Filters\Group $filterGroup
     */
    public function populateFilterGroup(Group $filterGroup)
    {

    }

    protected function onConfigureFilters(Filter $filter = null)
    {
        $group = new Group("AND");

        $this->populateFilterGroup($group);

        $filters = $group->getFilters();

        if (sizeof($filters) == 0) {
            // The search doesn't want to filter anything.
            return false;
        }

        if ($filter === null) {
            return $group;
        }

        $outer = new Group("AND");

        $outer->addFilters
        (
            $filter,
            $group
        );

        return $outer;
    }
}