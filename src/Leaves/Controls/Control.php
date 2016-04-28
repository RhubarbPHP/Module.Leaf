<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\BindableLeafInterface;
use Rhubarb\Leaf\Leaves\BindableLeafTrait;
use Rhubarb\Leaf\Leaves\Leaf;

class Control extends Leaf implements BindableLeafInterface
{
    use BindableLeafTrait;

    /**
     * @var ControlModel
     */
    protected $model;

    protected function getViewClass()
    {
        return ControlView::class;
    }
    
    protected function createModel()
    {
        return new ControlModel();
    }

    protected function onModelCreated()
    {
        $this->model->valueChangedEvent->attachHandler(function(){
            $this->getBindingValueChangedEvent()->raise();
        });
    }

    public function getBindingValue()
    {
        return $this->model->value;
    }

    public function setBindingValue($bindingValue)
    {
        $this->model->value = $bindingValue;
    }
}