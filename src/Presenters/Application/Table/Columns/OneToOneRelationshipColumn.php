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

namespace Rhubarb\Leaf\Presenters\Application\Table\Columns;

require_once __DIR__ . "/TableColumn.php";

use Rhubarb\Stem\Models\Model;
use Rhubarb\Stem\Schema\Relationships\OneToOne;

class OneToOneRelationshipColumn extends TableColumn
{
    /**
     * @var OneToOne
     */
    private $relationship;

    public function __construct(OneToOne $relationship, $label)
    {
        parent::__construct($label);

        $this->relationship = $relationship;
    }

    protected function getCellValue(Model $row, $decorator)
    {
        $object = $this->relationship->fetchFor($row);

        if ($object === null) {
            return "";
        }

        return $object->getLabel();
    }
}