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

use Rhubarb\Crown\Request\Request;
use Rhubarb\Leaf\Views\HtmlView;

class PagerView extends HtmlView
{
    public $numberOfPages;
    public $pageNumber;
    public $numberPerPage;
    public $suppressContent = false;

    /**
     * @var int  The number of pages around the boundaries to show before hiding page links in favour of an ellipsis
     */
    public $bufferPages = 3;

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

    /**
     * Sets the number of pages around the boundaries to show before hiding page links in favour of an ellipsis
     *
     * @param int $bufferPages
     */
    public function setBufferPages($bufferPages)
    {
        $this->bufferPages = $bufferPages;
    }

    public function printViewContent()
    {
        // Don't show any pages if there only is one page.
        if ($this->numberOfPages <= 1) {
            return;
        }

        $pages = [];
        $stub = $this->presenterPath;
        $request = Request::current();

        $iteration = 0;
        $class = 'first';
        while ($iteration < $this->numberOfPages) {
            $pageNumber = $iteration + 1;

            if ($pageNumber > $this->bufferPages && $pageNumber < $this->pageNumber - $this->bufferPages) {
                // If we're past the first few pages but are still a few pages before our selected page
                // and there is more than 1 page number to hide, show an ellipsis instead and skip forward
                $pages[] = '<span class="pager-buffer">&hellip;</span>';
                $iteration = $this->pageNumber - $this->bufferPages;
                continue;
            }
            if ($pageNumber < $this->numberOfPages - $this->bufferPages && $pageNumber > $this->pageNumber + $this->bufferPages - 1) {
                // If we're earlier than the last few pages but are a few pages after our selected page
                // and there is more than 1 page number to hide, show an ellipsis instead and skip forward
                $pages[] = '<span class="pager-buffer">&hellip;</span>';
                $iteration = $this->numberOfPages - $this->bufferPages;
                continue;
            }

            if ($pageNumber == $this->pageNumber) {
                $class .= ' selected';
            }

            $class .= ' pager-item';

            $class = ' class="' . trim($class) . '"';

            $pages[] = '<a href="' . $request->URI . '?' . $stub . '-page=' . $pageNumber . '"' . $class . ' data-page="' . $pageNumber . '">' . $pageNumber . '</a>';

            $class = '';

            $iteration++;
        }

        print "<div class=\"pager\"><div class=\"pages\">" . implode("", $pages) . "</div></div>";
    }
}
