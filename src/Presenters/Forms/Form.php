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

namespace Rhubarb\Leaf\Presenters\Forms;

require_once __DIR__ . "/../HtmlPresenter.php";
require_once __DIR__ . "/../ModelProvider.php";

use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;

/**
 * A presenter that emits an HTML form tag around it's view.
 *
 * This basic plumbing allows for HTTP post to initiate commands on the presenter.
 */
class Form extends HtmlPresenter
{
    use ModelProvider;

    public function __construct($name = "")
    {
        if ($name == "") {
            $name = basename(str_replace("\\", "/", get_class($this)));
        }

        parent::__construct($name);
    }
}
