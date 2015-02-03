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

namespace Rhubarb\Leaf\Presenters\Application\Table\FooterProviders;

require_once __DIR__ . '/SumFooterColumn.php';

use Rhubarb\Leaf\Presenters\Application\Table\Table;

class MoneySumFooterColumn extends SumFooterColumn
{
    public function getCellValue(Table $table)
    {
        return number_format(parent::getCellValue($table), 2);
    }
}