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

use Rhubarb\Leaf\Presenters\UrlStateLeafPresenter;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Leaves\Controls\Control;
use Rhubarb\Leaf\Presenters\ModelProvider;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Stem\Filters\Group;

/**
 * A search interface that raises search events on behalf of the contained search controls.
 *
 * @property bool $AutoSubmit True if searching should happen as you type.
 */
class SearchPanel extends UrlStateLeafPresenter
{
    use ModelProvider;

    private $defaultControlValues = [];

    private $searchControlsColumnCount = 6;

    public $SearchButton = 'Search';

    /**
     * An array with keys matching search control names and values defining what URL GET param names they should have
     *
     * @var string[]
     */
    public $urlStateNames = [];

    /**
     * Data from URL GET params matching controls based on their names in $urlStateNames
     *
     * @var array
     */
    public $urlStateValues = [];

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

        if (!isset($this->model->SearchButton)) {
            $this->model->SearchButton = $this->SearchButton;
        }

        if (!isset($this->model->urlStateNames)) {
            $this->model->urlStateNames = $this->urlStateNames;
        }
    }

    protected function getPublicModelPropertyList()
    {
        $list = parent::getPublicModelPropertyList();
        $list[] = "AutoSubmit";
        $list[] = 'urlStateNames';
        $list[] = 'SearchButton';

        return $list;
    }

    protected final function snapshotDefaultControlValues()
    {
        $controls = $this->getSearchControls();
        $defaultValues = $this->getDefaultControlValues();

        foreach ($controls as $control) {
            if (!isset($defaultValues[$control->getName()])) {
                $defaultValues[$control->getName()] = "";
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

        $this->urlStateNames = $this->getUrlStateNames($this->searchControls);

        return $this->searchControls;
    }

    /**
     * Return URL GET param names for the controls in this panel
     *
     * @param Control[] $searchControls
     * @return \string[] An array with keys matching the control names and values defining the GET param names
     */
    protected function getUrlStateNames(array $searchControls)
    {
        $names = [];
        foreach ($searchControls as $control) {
            $name = $control->getName();
            $names[$name] = StringTools::camelCaseToSeparated($name);
        }

        return $names;
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

        $this->view->attachEventHandler("GetUrlStateNames", function () {
            return $this->urlStateNames;
        });

        $this->view->attachEventHandler("Search", function () {
            $this->raiseEvent("Search");
        });
    }

    protected function bindEvents(Presenter $presenter)
    {
        $presenter->attachEventHandler("GetFilter", [$this, "OnGetFilter"]);
        $presenter->attachEventHandler("GetSearchControlValues", [$this, "GetSearchControlValues"]);
    }

    /**
     * Override this method to create any filters that are required.
     *
     * @param \Rhubarb\Stem\Filters\Group $filterGroup
     */
    public function populateFilterGroup(Group $filterGroup)
    {

    }

    protected function onGetFilter()
    {
        $group = new Group("AND");

        $this->populateFilterGroup($group);

        $filters = $group->getFilters();

        if (sizeof($filters) == 0) {
            // The search doesn't want to filter anything.
            return null;
        }

        return $group;
    }
}
