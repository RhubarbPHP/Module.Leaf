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

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__ . "/../Views/View.php";

use Rhubarb\Leaf\Views\HtmlView;

/**
 * This simple view presents a single sub presenter for the 'step' that should
 * current be shown.
 */
class SwitchedPresenterView extends HtmlView
{
    private $presenter;

    public function createPresenters()
    {
        // We need to register our sub presenter early to make sure it's included
        // in the events processing loop.
        $this->presenter = $this->raiseEvent("GetCurrentPresenter");

        $this->addPresenters($this->presenter);
    }

    public function printViewContent()
    {
        print $this->presenter;
    }
}