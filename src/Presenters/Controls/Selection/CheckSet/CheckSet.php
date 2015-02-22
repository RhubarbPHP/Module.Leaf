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

namespace Rhubarb\Leaf\Presenters\Controls\Selection\CheckSet;

require_once __DIR__ . '/../DropDown/DropDown.php';

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;

class CheckSet extends DropDown
{
    protected function supportsMultipleSelection()
    {
        return true;
    }

    protected function createView()
    {
        return new CheckSetView();
    }

    protected function parseRequestForCommand()
    {
        $request = Context::currentRequest();

        if ($request->Server("REQUEST_METHOD") == "POST") {
            $values = $request->Post($this->getIndexedPresenterPath());

            if ($values === null) {
                $values = [];
            }

            if (!is_array($values)) {
                $values = explode(",", $values);
            }

            $this->setSelectedItems($values);
            $this->setBoundData();
        }
    }
}