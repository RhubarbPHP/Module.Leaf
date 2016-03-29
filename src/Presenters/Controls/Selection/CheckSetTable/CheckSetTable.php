<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\CheckSetTable;

use Rhubarb\Leaf\Presenters\Controls\Selection\CheckSet\CheckSet;

/**
 * Horizontally oriented checkboxes
 */
class CheckSetTable extends CheckSet
{
    protected function createView()
    {
        return new CheckSetTableView();
    }
}
