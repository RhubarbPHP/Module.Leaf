<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Leaf\Leaves\Leaf;

class Control extends Leaf
{
    protected function getViewClass()
    {
        return ControlView::class;
    }
    
    protected function createModel()
    {
        $model = new ControlModel();
        // Set initial model values and initialise event objects
        // e.g. $model->saveEvent = new Event();
        return $model;
    }
}