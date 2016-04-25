<?php

namespace Rhubarb\Leaf\Presenters\Controls;

use Rhubarb\Leaf\Presenters\PresenterModel;

class ControlModel extends PresenterModel
{
    public $value;

    /**
     * @var Event
     */
    public $valueChanged;
}