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

namespace Rhubarb\Leaf\LayoutProviders;

/**
 * A simple container for a collection of objects that need to be printed in sequence.
 *
 * Used for printing multiple objects as the value of a key/value pair.
 */
class PrintableList
{
    protected $objects = [];

    /**
     * @param $objects mixed An array of objects or a variable number of objects as arguments.
     */
    public function __construct($objects)
    {
        if (!is_array($objects)) {
            $objects = func_get_args();
        }

        $this->objects = $objects;
    }

    function __toString()
    {
        return implode("", $this->objects);
    }
}