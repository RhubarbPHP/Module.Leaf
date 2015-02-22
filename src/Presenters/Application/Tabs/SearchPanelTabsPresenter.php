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

require_once __DIR__ . '/TabsPresenter.php';

use Rhubarb\Leaf\Presenters\Application\Search\SearchPanel;
use Rhubarb\Leaf\Presenters\Presenter;

class SearchPanelTabsPresenter extends TabsPresenter
{
    protected function onSelectedTabChanged($tabIndex)
    {
        parent::onSelectedTabChanged($tabIndex);

        // If the tab that's been selected has control values attached, throw an event to say so.
        $tab = $this->getTabByIndex($tabIndex);

        if ($tab instanceof SearchPanelTabDefinition) {
            $this->raiseEvent("OnSearchBoundTabSelected", $tab);
        }
    }

    protected function inflateTabDefinitions()
    {
        $inflatedTabDefinitions = [];

        foreach ($this->tabs as $key => $value) {
            if ($value instanceof TabDefinition) {
                $inflatedTabDefinitions[] = $value;
            } elseif (is_string($key)) {
                if (is_array($value)) {
                    $inflatedTabDefinitions[] = new SearchPanelTabDefinition($key, $value);
                } else {
                    $inflatedTabDefinitions[] = new TabDefinition($key, $value);
                }
            }
        }

        return $inflatedTabDefinitions;
    }

    protected function markSelectedTab(&$inflatedTabDefinitions)
    {
        $currentSearchValues = $this->raiseEvent("GetSearchControlValues");

        $anySelected = false;

        if ($currentSearchValues !== null) {
            foreach ($inflatedTabDefinitions as $tab) {
                $same = true;

                foreach ($tab->data as $key => $value) {
                    if (!isset($currentSearchValues[$key])) {
                        $same = false;
                        break;
                    }

                    if ($currentSearchValues[$key] !== $value) {
                        $same = false;
                        break;
                    }
                }

                foreach ($currentSearchValues as $key => $value) {
                    if (!isset($tab->data[$key])) {
                        if ($value !== false && $value !== null && $value !== "") {
                            $same = false;
                            break;
                        }

                        continue;
                    }

                    if ($tab->data[$key] !== $value) {
                        $same = false;
                        break;
                    }
                }

                if ($same) {
                    $anySelected = true;
                    $tab->selected = true;
                } else {
                    $tab->selected = false;
                }
            }
        } else {
            $currentSearchValues = [];
        }

        if (!$anySelected) {
            $inflatedTabDefinitions[] = $searchResults = new SearchResultsTabDefinition("Search Results");
            $searchResults->data = $currentSearchValues;
            $searchResults->selected = true;
        }
    }

    protected function bindEvents(Presenter $presenter)
    {
        if ($presenter instanceof SearchPanel) {
            $this->attachEventHandler(
                "OnSearchBoundTabSelected",
                function (SearchPanelTabDefinition $tabDefinition) use ($presenter) {
                    $presenter->setSearchControlValues($tabDefinition->data);
                }
            );

            $presenter->attachEventHandler(
                "Search",
                function () {
                    $this->rePresent();
                }
            );
        }
    }
}