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

namespace Rhubarb\Leaf\Presenters\Application\Pager;

require_once __DIR__ . "/../../HtmlPresenter.php";

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Exceptions\PagerOutOfBoundsException;
use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Stem\Collections\Collection;

/**
 * @property Collection $Collection The collection to page
 */
class Pager extends HtmlPresenter
{
    /**
     * Indicates whether or not the pager has changed the range of the collection.
     *
     * @var bool
     */
    public $collectionRangeModified = false;

    public function __construct(Collection $collection = null, $perPage = 50, $name = "")
    {
        parent::__construct($name);

        $this->Collection = $collection;

        $this->model->PerPage = $perPage;
        $this->model->PageNumber = 1;

        $this->attachEventHandler("PageChanged", function ($pageNumber) {
            $this->setPageNumber($pageNumber);
        });
    }

    public function setCollection(Collection $collection)
    {
        $this->Collection = $collection;
    }

    protected function getPublicModelPropertyList()
    {
        $properties = parent::getPublicModelPropertyList();

        $properties[] = "PerPage";
        $properties[] = "PageNumber";

        return $properties;
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->suppressContent = $this->suppressContent;

        $this->view->attachEventHandler("PageChanged", function ($pageNumber) {
            $this->setPageNumber($pageNumber);
        });
    }

    public function setPageNumber($pageNumber)
    {
        $numberOfPages = $this->calculateNumberOfPages();

        if ($pageNumber > max($numberOfPages, 1)) {
            throw new PagerOutOfBoundsException();
        }

        $this->NumberOfPages = $numberOfPages;
        $this->collectionRangeModified = true;
        $this->PageNumber = $pageNumber;

        $this->Collection->setRange((($pageNumber - 1) * $this->PerPage), $this->PerPage);
    }

    protected function parseRequestForCommand()
    {
        $key = $this->PresenterPath . "-page";

        $request = Context::currentRequest();

        if ($request->Request($key)) {
            $this->raiseEvent("PageChanged", $request->Request($key));
        }

        parent::parseRequestForCommand();
    }

    public function setNumberPerPage($perPage)
    {
        $this->model->PerPage = $perPage;
    }

    protected function createView()
    {
        return new PagerView();
    }

    protected function beforeRenderView()
    {
        // Note that we're using before render view as the calculation of the page numbers
        // involves fetching our collection, however ApplyModelToView gets called twice,
        // once before events and once after events. If however the event processing would mean
        // that the data our collection uses is modified and because the collection is already
        // fetched, any presenters using the collection won't see the modification.

        $this->setPageNumber($this->model->PageNumber);

        $this->view->setNumberOfPages($this->NumberOfPages);
        $this->view->setNumberPerPage($this->model->PerPage);
        $this->view->setPageNumber($this->model->PageNumber);
        $this->view->setPath($this->PresenterPath);

        parent::beforeRenderView();
    }

    /**
     * @return float
     */
    private function calculateNumberOfPages()
    {
        $this->Collection->setRange(0, $this->model->PerPage);

        $collectionSize = sizeof($this->Collection);
        $pages = ceil($collectionSize / $this->model->PerPage);

        return $pages;
    }
}