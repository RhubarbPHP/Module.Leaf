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

require_once __DIR__ . "/Pager.php";

use Rhubarb\Stem\Collections\Collection;

class FormPager extends Pager
{
    public function __construct(Collection $collection, $perPage = 50, $name = "")
    {
        parent::__construct($collection, $perPage, $name);

        $this->attachClientSidePresenterBridge = true;
    }

    protected function getClientSideViewBridgeName()
    {
        return "FormPager";
    }

    protected function getClientSidePresenterBridgeScriptUrls()
    {
        return [
            "/mvp/jquery-presenter.js",
            "/mvp/application/form-pager.js"
        ];
    }

    protected function createView()
    {
        return new FormPagerView();
    }
}