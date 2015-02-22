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

require_once __DIR__ . "/../../HtmlPresenter.php";

use Rhubarb\Crown\Exceptions\ForceResponseException;
use Rhubarb\Crown\Integration\DataStreams\CsvStream;
use Rhubarb\Crown\Response\FileResponse;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\ModelColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\PresenterColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\SortableColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\TableColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\Template;
use Rhubarb\Leaf\Presenters\Application\Table\FooterProviders\FooterProvider;
use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Decorators\DataDecorator;
use Rhubarb\Stem\Filters\Filter;
use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\ForeignKey;
use Rhubarb\Stem\Schema\Relationships\OneToOne;
use Rhubarb\Stem\Schema\SolutionSchema;

/**
 * Presents an HTML table using a ModelList as it's data source
 * @property string $NoDataHtml    The HTML to show instead of the table in the event that there are no rows to display.
 * @property string $UnsearchedHtml The HTML to show instead of the table before a search has been performed.
 * @property string $RepeatPagerAtBottom   Ability to set if you want to display pager at bottom.
 * @property array $Columns            A dictionary of column names.
 * @property array $ExportColumns    A dictionary of column names for export.
 */
class Table extends HtmlPresenter
{
    private $collection;
    private $pageSize;
    private $footerProviders = [];
    private $tableCssClassNames = array();

    /**
     * @var Model
     */
    private $currentRow;

    public function __construct(Collection $list = null, $pageSize = 50, $presenterName = "Table")
    {
        parent::__construct($presenterName);

        $this->collection = $list;
        $this->Columns = array();
        $this->pageSize = $pageSize;
        $this->tableCssClassNames = array();

        $this->attachClientSidePresenterBridge = true;
    }

    public function addFooter(FooterProvider $provider)
    {
        $provider->setTable($this);
        $this->footerProviders[] = $provider;
    }

    public function clearFooters()
    {
        $this->footerProviders = [];
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function exportList()
    {
        $this->configureFilters();

        if (file_exists("cache/export.csv")) {
            unlink("cache/export.csv");
        }

        $file = "cache/export.csv";

        $stream = new CsvStream($file);

        $columns = $this->inflateColumns($this->ExportColumns);
        $headings = [];

        foreach ($columns as $column) {
            $headings[] = $column->label;
        }

        $stream->setHeaders(
            $headings
        );

        foreach ($this->collection as $item) {
            $data = [];

            $decorator = DataDecorator::getDecoratorForModel($item);

            if (!$decorator) {
                $decorator = $item;
            }

            foreach ($columns as $column) {
                $data[$column->label] = $column->getCellContent($item, $decorator);
            }

            $stream->appendItem($data);
        }

        // Push this file to the browser.
        throw new ForceResponseException(new FileResponse($file));
    }

    public function setCollection($collection)
    {
        $this->collection = $collection;
    }

    protected function initialiseModel()
    {
        parent::initialiseModel();

        $this->SortColumn = "";
        $this->SortDirection = "";
        $this->Searched = false;
    }

    protected function getPublicModelPropertyList()
    {
        $properties = parent::getPublicModelPropertyList();

        $properties[] = "SortColumn";
        $properties[] = "SortDirection";
        $properties[] = "Searched";

        return $properties;
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->attachEventHandler("CurrentRow", function ($row) {
            $this->currentRow = $row;
        });

        $this->view->attachEventHandler("PageChanged", function () {
            $this->onRefresh();
            $this->raiseEventOnViewBridge($this->getPresenterPath(), "OnPageChanged");
        });

        $this->view->attachEventHandler("GetCollection", function () {
            return $this->collection;
        });

        $this->view->attachEventHandler("GetRowCssClasses", function ($model, $rowNumber) {
            return $this->raiseEvent("GetRowCssClasses", $model, $rowNumber);
        });

        $this->view->attachEventHandler("GetAdditionalClientSideRowData", function ($model, $rowNumber) {
            return $this->raiseEvent("GetAdditionalClientSideRowData", $model, $rowNumber);
        });

        $this->view->attachEventHandler("ColumnClicked", function ($index) {
            // Get the inflated columns so we know which one we're dealing with.
            $columns = $this->inflateColumns($this->Columns);
            $column = $columns[$index];

            if ($column instanceof SortableColumn) {
                // Change the sort order.
                $this->changeSort($column->getSortableColumnName());
            }

            return $index;
        });
    }

    protected function changeSort($columnName)
    {
        $currentDirection = false;

        if ($this->SortColumn == $columnName) {
            $currentDirection = ($this->SortDirection) ? $this->SortDirection : false;
        }

        $currentDirection = !$currentDirection;

        $this->SortColumn = $columnName;
        $this->SortDirection = $currentDirection;

        $this->rePresent();
    }

    protected function applySort()
    {
        if ($this->SortColumn) {
            $this->collection->replaceSort($this->SortColumn, $this->SortDirection);
        }
    }

    protected function createView()
    {
        return new TableView();
    }

    private $schemaColumns = false;

    private function getSchemaColumns()
    {
        if (!$this->schemaColumns) {
            $schema = $this->collection->getModelSchema();
            $this->schemaColumns = $schema->getColumns();
        }

        return $this->schemaColumns;
    }

    protected function createColumnFromString($columnName, $label)
    {
        $modelClassName = SolutionSchema::getModelClass($this->collection->GetModelClassName());

        $autoLabelled = false;

        if ($label === null) {
            $label = StringTools::wordifyStringByUpperCase($columnName);
            $autoLabelled = true;
        }

        $schemaColumns = $this->getSchemaColumns();

        // Try and convert this to a ModelColumn
        if (isset($schemaColumns[$columnName])) {
            if ($schemaColumns[$columnName] instanceof ForeignKey) {
                $relationships = SolutionSchema::getAllOneToOneRelationshipsForModelBySourceColumnName($this->collection->GetModelClassName());

                if (isset($relationships[$columnName])) {
                    if ($relationships[$columnName] instanceof OneToOne) {
                        return new Columns\OneToOneRelationshipColumn($relationships[$columnName], $label);
                    }
                }
            }

            return ModelColumn::createTableColumnForSchemaColumn($schemaColumns[$columnName], $label);
        } else {
            // If the property exists as a computed column let's use that.
            if (method_exists($modelClassName, "Get" . $columnName)) {
                // Let this computed column be treated as a normal String model column.
                return new ModelColumn($columnName, $label);
            }

            if (preg_match("/^[.\w]+$/", $columnName)) {
                // If it's all characters and contains a full stop it must be a navigation property.
                if (preg_match("/\./", $columnName)) {
                    if ($autoLabelled) {
                        $parts = explode(".", $columnName);
                        $label = StringTools::wordifyStringByUpperCase($parts[sizeof($parts) - 1]);
                    }

                    return new Template("{" . $columnName . "}", $label);
                } else {
                    $relationships = SolutionSchema::getAllRelationshipsForModel($this->collection->GetModelClassName());

                    if (isset($relationships[$columnName])) {
                        if ($relationships[$columnName] instanceof OneToOne) {
                            return new Columns\OneToOneRelationshipColumn($relationships[$columnName], $label);
                        }
                    }
                }
            }

            return new Columns\Template($columnName, $label);
        }
    }

    protected function createColumnFromObject($object, $label)
    {
        if ($object instanceof Presenter) {
            return new PresenterColumn($object, $label);
        }

        return false;
    }

    /**
     * Expands the columns array, creating TableColumn objects where needed.
     */
    protected function inflateColumns($columns)
    {
        // If the collection itself is null, we can't return a sensible columns collection.
        // This might happen if the collection isn't determined until just before printing.
        if ($this->collection == null) {
            return [];
        }

        $inflatedColumns = array();

        foreach ($columns as $key => $value) {
            $tableColumn = $value;

            $label = (!is_numeric($key)) ? $key : null;

            if (is_string($tableColumn)) {
                $value = (string)$value;
                $tableColumn = $this->createColumnFromString($value, $label);
            } elseif (!($tableColumn instanceof TableColumn)) {
                $tableColumn = $this->createColumnFromObject($tableColumn, $label);
            }

            if ($tableColumn && ($tableColumn instanceof TableColumn)) {
                if ($tableColumn instanceof PresenterColumn) {
                    $tableColumn->getPresenter()->replaceEventHandler("GetBoundData", function ($dataKey, $viewIndex = false) {
                        return $this->getDataForPresenter($dataKey, $viewIndex);
                    });
                }

                $inflatedColumns[] = $tableColumn;
            }
        }

        return $inflatedColumns;
    }

    /**
     * Provides model data to the requesting presenter.
     *
     * This implementation ensures the PresenterColumns are effectively receive data from the table row
     *
     * @param string $dataKey
     * @param bool|int $viewIndex
     * @return mixed
     */
    protected function getDataForPresenter($dataKey, $viewIndex = false)
    {
        if (!isset($this->currentRow[$dataKey])) {
            return $this->raiseEvent("GetBoundData", $dataKey, $viewIndex);
        }

        $value = $this->currentRow[$dataKey];

        if ($value instanceof Model) {
            return $value->UniqueIdentifier;
        }

        return $value;
    }

    private function configureFilters()
    {
        $newFilter = $this->raiseEvent("ConfigureFilters", $this->collection->getFilter());

        if ($newFilter !== null && $newFilter instanceof Filter) {
            $this->collection->replaceFilter($newFilter);
        }

        $this->applySort();
    }

    protected function beforeRenderView()
    {
        $this->configureFilters();
        $this->raiseEvent("BeforeRenderView");
    }

    protected function applyModelToView()
    {
        $columns = $this->inflateColumns($this->Columns);

        $this->view->footerProviders = $this->footerProviders;
        $this->view->list = $this->collection;
        $this->view->columns = $columns;
        $this->view->pageSize = $this->pageSize;
        $this->view->noDataHtml = $this->NoDataHtml;
        $this->view->unsearchedHtml = $this->UnsearchedHtml;
        $this->view->repeatPagerAtBottom = $this->RepeatPagerAtBottom;
        $this->view->tableCssClasses = $this->tableCssClassNames;
    }

    protected function getData($dataKey, $viewIndex = false)
    {
        if ($dataKey == "Searched") {
            return $this->model->Searched;
        }
        return parent::getData($dataKey, $viewIndex);
    }

    protected function bindEvents(Presenter $presenter)
    {
        $presenter->attachEventHandler("Search", function () {
            $this->setSearched();
            $this->onRefresh();
        });

        $presenter->attachEventHandler("Updated", array($this, "OnRefresh"));
    }

    protected function onRefresh()
    {
        $this->rePresent();
    }

    public function setSearched()
    {
        $this->model->Searched = true;
    }

    public function addTableCssClass($classNames)
    {
        $classes = $this->tableCssClassNames;

        if (!is_array($classes)) {
            $classes = [];
        }

        $classes = array_merge($classes, $classNames);
        $this->tableCssClassNames = $classes;
    }
}
