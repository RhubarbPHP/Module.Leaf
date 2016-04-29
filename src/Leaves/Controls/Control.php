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
        $this->model->valueChangedEvent->attachHandler(function($index = null){
            $this->getBindingValueChangedEvent()->raise($index);
        });
    }

    protected function beforeRender()
    {
        $this->model->value = $this->getBindingValueRequestedEvent()->raise($this->model->leafIndex);
    }

    public function getBindingValue()
    {
        return $this->model->value;
    }

    public function setBindingValue($bindingValue)
    {
        $this->model->value = $bindingValue;
    }

    public function addCssClassNames(...$classNames)
    {
        $this->model->addCssClassNames(...$classNames);
    }

    public function addHtmlAttribute($attributeName, $attributeValue)
    {
        $this->model->addHtmlAttribute($attributeName, $attributeValue);
    }
}