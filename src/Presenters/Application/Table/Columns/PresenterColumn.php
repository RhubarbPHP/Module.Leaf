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

require_once __DIR__ . '/TableColumn.php';
require_once __DIR__ . '/../../../Presenter.php';

use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Stem\Models\Model;

/**
 * A column type which asks another presenter to present inside each cell.
 */
class PresenterColumn extends TableColumn
{
    /**
     * @var Presenter
     */
    protected $presenter;

    public function __construct(Presenter $presenter, $label = "")
    {
        parent::__construct($label);

        $this->presenter = $presenter;
    }

    public function getPresenter()
    {
        return $this->presenter;
    }

    /**
     * Implement this to return the content for a cell.
     *
     * @param \Rhubarb\Stem\Models\Model $row
     * @param \Rhubarb\Stem\Decorators\DataDecorator $decorator
     * @return mixed
     */
    protected function getCellValue(Model $row, $decorator)
    {
        ob_start();

        $this->presenter->displayWithIndex($row->UniqueIdentifier);

        return ob_get_clean();
    }
}