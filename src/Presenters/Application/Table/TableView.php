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

namespace Rhubarb\Leaf\Presenters\Application\Table;

require_once __DIR__ . "/../../../Views/JQueryView.php";

use Rhubarb\Leaf\Presenters\Application\Pager\EventPager;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\SortableColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\Template;
use Rhubarb\Leaf\Views\JQueryView;
use Rhubarb\Stem\Aggregates\Count;
use Rhubarb\Stem\Decorators\DataDecorator;

class TableView extends JQueryView
{
    /**
     * @var \Rhubarb\Stem\Collections\Collection
     */
    public $list;

    public $columns = array();

    public $noDataHtml = "";

    public $unsearchedHtml = "";

    public $searched = false;

    public $pageSize;

    public $footerProviders = [];

    public $repeatPagerAtBottom = false;

    public $tableCssClasses = array();

    protected function getClientSideViewBridgeName()
    {
        return "TableViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/TableViewBridge.js";

        return $package;
    }

    public function createPresenters()
    {
        $pager = new EventPager();

        $this->addPresenters(
            [
                "pager" => $pager
            ]
        );

        $this->presenters["pager"]->attachEventHandler("PageChanged", function () {
            $this->raiseEvent("PageChanged");
        });
    }

    protected function ConfigurePresenters()
    {
        $this->presenters["pager"]->setCollection($this->raiseEvent("GetCollection"));
        $this->presenters["pager"]->setNumberPerPage($this->pageSize);
    }

    public function printViewContent()
    {
        $suppressPagerContent = false;

        try {
            list($count) = $this->list->calculateAggregates(new Count($this->list->getModelSchema()->uniqueIdentifierColumnName));
        } catch (\Exception $ex) {
            $count = sizeof($this->list);
        }
        if ($count == 0 && $this->noDataHtml) {
            print $this->noDataHtml;
            $suppressPagerContent = true;
        }

        if ($this->unsearchedHtml && !$this->getData("Searched")) {
            print $this->unsearchedHtml;
            $suppressPagerContent = true;
        }
        //Always print the pager so we get javaScript loading
        $this->presenters["pager"]->setSuppressContent($suppressPagerContent);
        print $this->presenters["pager"];

        if ($suppressPagerContent) {
            return;
        }

        ?>
        <div class='list'>
            <table class="<?= $this->getTableCssClass(); ?>">
                <thead>
                <tr>
                    <?php

                    $sorts = $this->list->getSorts();

                    foreach ($this->columns as $column) {
                        $classes = $column->getCssClasses();

                        if ($column instanceof SortableColumn) {
                            $classes[] = "sortable";

                            if (isset($sorts[$column->getSortableColumnName()])) {
                                $classes[] = "sorted";

                                if ($sorts[$column->getSortableColumnName()] == false) {
                                    $classes[] = "descending";
                                }
                            }
                        }

                        $classString = implode(" ", $classes);

                        if ($classString != "") {
                            $classString = " class=\"" . $classString . "\"";
                        }

                        print "\r\n\t\t\t\t\t<th" . $classString . ">" . $column->label . "</th>";
                    }

                    ?>
                </tr>
                </thead>
                <tbody>
                <?php

                $rowNumber = 0;
                foreach ($this->list as $model) {
                    $this->raiseEvent("CurrentRow", $model);

                    $classes = $this->raiseEvent("GetRowCssClasses", $model, $rowNumber);

                    $classString = "";
                    if (!empty($classes) && is_array($classes)) {
                        $classString = implode(" ", $classes);

                        if ($classString != "") {
                            $classString = " class=\"" . $classString . "\"";
                        }
                    }

                    $rowData = $this->raiseEvent("GetAdditionalClientSideRowData", $model, $rowNumber);

                    $rowDataString = "";
                    if (is_array($rowData) && count($rowData)) {
                        $rowDataString .= " data-row-data=\"" . htmlentities(json_encode($rowData)) . "\"";
                    }

                    print "\r\n\t\t\t\t<tr data-row-id=\"" . $model->UniqueIdentifier . "\"$classString$rowDataString>";

                    $decorator = DataDecorator::getDecoratorForModel($model);

                    if (!$decorator) {
                        $decorator = $model;
                    }

                    foreach ($this->columns as $column) {
                        $cellContent = $column->getCellContent($model, $decorator);

                        $classes = $column->getCssClasses();


                        if (!($column instanceof Template && (preg_match("/<a/", $cellContent)))) {
                            $classes[] = "clickable";
                        }

                        $classString = implode(" ", $classes);

                        if ($classString != "") {
                            $classString = " class=\"" . $classString . "\"";
                        }

                        $customAttributes = $column->getCustomCellAttributes($model);
                        $customAttributesString = "";

                        if (sizeof($customAttributes) > 0) {
                            foreach ($customAttributes as $name => $value) {
                                $customAttributesString .= " " . $name . "=\"" . htmlentities($value) . "\"";
                            }
                        }

                        print "\r\n\t\t\t\t\t<td" . $classString . $customAttributesString . ">" . $cellContent . "</td>";
                    }

                    print "\r\n\t\t\t\t</tr>";

                    $rowNumber++;
                }

                ?>
                </tbody>
                <?php

                if (sizeof($this->footerProviders) > 0) {
                    print "<tfoot>";

                    foreach ($this->footerProviders as $provider) {
                        $provider->printFooter();
                    }

                    print "</tfoot>";
                }

                ?>
            </table>
        </div>
        <?php

        if ($this->repeatPagerAtBottom) {
            $this->presenters["pager"]->displayWithIndex("bottom");
        }
    }


    public function getTableCssClass()
    {
        return implode(" ", $this->tableCssClasses);
    }
}
