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

require_once __DIR__ . "/../../../Views/HtmlView.php";

use Rhubarb\Leaf\Presenters\UrlStateView;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Presenters\Controls\Buttons\Button;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class SearchPanelView extends UrlStateView
{
    protected function getClientSideViewBridgeName()
    {
        return "SearchPanelViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/" . $this->getClientSideViewBridgeName() . ".js";

        return $package;
    }

    public $urlStateNames = [];

    /** @var ControlPresenter[] */
    protected $controls = [];
    protected $searchControlsColumnCount = 3;

    public function setSearchControlsColumnCount($columns = 6)
    {
        $this->searchControlsColumnCount = $columns;
    }

    public function createPresenters()
    {
        parent::createPresenters();

        $this->controls = $this->raiseEvent("GetControls");

        $this->urlStateNames = $this->raiseEvent("GetUrlStateNames");
        $this->raiseEvent("pagerUrlStateNameChanged");

        $this->addPresenters($this->controls);

        $searchButton = new Button($this->getModel()->SearchButton, "Search", function () {
            $this->raiseEvent("Search");
        }, true);

        $searchButton->attachClientSidePresenterBridge = true;
        $this->addPresenters($searchButton);
    }

    protected function printViewContent()
    {
        print '<div class="search-panel">
					<table class="grid">
						<tr>';

        $count = 1;
        foreach ($this->controls as $control) {
            print '<td><label for="' . $control->getIndexedPresenterPath() . '">' . $control->getLabel() . '</label>' . $control . '</td>';

            if ($count % $this->searchControlsColumnCount == 0) {
                print "</tr><tr>";
            }

            $count++;
        }

        print '<td>' . $this->presenters[$this->getModel()->SearchButton] . '</td>';

        print '</tr></table></div>';
    }

    public function parseUrlState(WebRequest $request)
    {
        /**
         * To ensure child leaves process their raw request values in the normal way we mutate the
         * post data to change our shortened keys to those that match the leaf path of the child
         * leaf.
         */
        foreach ($this->urlStateNames as $controlName => $paramName) {
            if ($request->get($paramName) !== null) {
                foreach ($this->controls as $control) {
                    if ($control->getName() == $controlName) {
                        $path = $control->getPresenterPath();
                        if (!isset($request->modelData['PostData'][$path])) {
                            $request->modelData['PostData'][$path] = $request->get($paramName);
                        }
                        break;
                    }
                }
            }
        }
    }
}
