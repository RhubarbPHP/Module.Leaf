<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Views\View;

abstract class ControlView extends View
{
    /**
     * @var ControlModel
     */
    protected $model;

    protected function parseRequest(WebRequest $request)
    {
        // By default if a control can be represented by a single HTML element then the name of that element
        // should equal the leaf path of the control. If that is true then we can automatically discover and
        // update our model.
        $value = $request->post($this->model->leafPath);

        if ($value !== null){
            $this->model->setValue($value);
        }
    }
}