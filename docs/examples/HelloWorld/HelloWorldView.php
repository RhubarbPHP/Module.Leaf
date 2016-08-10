<?php

namespace Rhubarb\Leaf\Examples\HelloWorld;

use Rhubarb\Leaf\Views\View;

class HelloWorldView extends View
{
    /**
     * @var HelloWorldModel
     */
    protected $model;

    protected function printViewContent()
    {
        ?><p><?=$this->model->name;?>, Hello World!</p><?php
    }
}