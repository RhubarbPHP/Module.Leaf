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

require_once __DIR__ . "/../../../Views/HtmlView.php";

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Views\HtmlView;

class PagerView extends HtmlView
{
    public $numberOfPages;
    public $pageNumber;
    public $numberPerPage;
    public $suppressContent = false;

    public function setNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
    }

    public function setPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    public function setNumberPerPage($numberPerPage)
    {
        $this->numberPerPage = $numberPerPage;
    }

    public function printViewContent()
    {
        // Don't show any pages if there only is one page.
        if ($this->numberOfPages <= 1) {
            return;
        }

        $pageStart = max(0, $this->pageNumber - 5);
        $pageEnd = min($this->numberOfPages, $pageStart + 9);

        $pages = [];
        $stub = $this->presenterPath;
        $request = Context::currentRequest();

        for ($x = $pageStart; $x < $pageEnd; $x++) {
            $pageNumber = $x + 1;

            $class = ($x == $pageStart) ? "first" : "";

            if ($pageNumber == $this->pageNumber) {
                $class .= " selected";
            }

            $class .= " pager-item";

            $class = (trim($class) != "") ? " class=\"" . $class . "\"" : "";

            $pages[] = "<a href=\"" . $request->URI . "?" . $stub . "-page=" . $pageNumber . "\"" . $class . " data-page=\"" . $pageNumber . "\">" . $pageNumber . "</a>";
        }

        print "<div class=\"pager\"><div class=\"pages\">" . implode("", $pages) . "</div></div>";
    }
}