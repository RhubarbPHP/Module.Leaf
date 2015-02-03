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

namespace Rhubarb\Leaf\Presenters\Application\Tabs;

require_once __DIR__ . '/../../HtmlPresenter.php';

use Rhubarb\Leaf\Presenters\HtmlPresenter;

class TabsPresenter extends HtmlPresenter
{
    protected $tabs = [];

    protected function createView()
    {
        return new TabsView();
    }

    public function setTabDefinitions($tabs = [])
    {
        $this->tabs = $tabs;
    }

    public function getTabDefinitions()
    {
        return $this->tabs;
    }

    public function getTabByIndex($tabIndex)
    {
        $tabs = $this->getInflatedTabDefinitions();

        return $tabs[$tabIndex];
    }

    protected final function getInflatedTabDefinitions()
    {
        $tabs = $this->inflateTabDefinitions();
        $this->markSelectedTab($tabs);

        return $tabs;
    }

    protected function inflateTabDefinitions()
    {
        $inflatedTabDefinitions = [];

        foreach ($this->tabs as $key => $value) {
            if ($value instanceof TabDefinition) {
                $inflatedTabDefinitions[] = $value;
            } elseif (is_string($key)) {
                $inflatedTabDefinitions[] = new TabDefinition($key, $value);
            }
        }

        return $inflatedTabDefinitions;
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->attachEventHandler(
            "TabSelected",
            function ($tabIndex) {
                $this->selectTabByIndex($tabIndex);
            }
        );
    }

    protected function markSelectedTab(&$inflatedTabDefinitions)
    {
        if ($this->SelectedTab !== null) {
            $inflatedTabDefinitions[$this->SelectedTab]->selected = true;
        }
    }

    protected function applyModelToView()
    {
        $tabs = $this->getInflatedTabDefinitions();

        $this->view->setTabDefinitions($tabs);

        parent::applyModelToView();
    }

    /**
     * Set's the selected tab to the one indexed by $index
     *
     * Triggers the SelectedTabChanged event.
     *
     * @param $tabIndex
     */
    public function selectTabByIndex($tabIndex)
    {
        $this->model->SelectedTab = $tabIndex;

        $this->raiseEvent("SelectedTabChanged", $tabIndex);

        $this->onSelectedTabChanged($tabIndex);
    }

    /**
     * Override to perform actions when the selected tab changes.
     */
    protected function onSelectedTabChanged($tabIndex)
    {

    }
}