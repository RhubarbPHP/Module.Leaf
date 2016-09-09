<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Crown\String\StringTools;
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
        $this->setValue($this->getBindingValueRequestedEvent()->raise($this->model->leafIndex));
    }

    public function getValue()
    {
        return $this->model->value;
    }

    public function getPath()
    {
        return $this->model->leafPath;
    }

    public function setValue($bindingValue)
    {
        $this->model->value = $bindingValue;
    }

    public function setLabel($labelText)
    {
        $this->model->label = $labelText;
    }

    public function setPlaceholderText($placeholderText)
    {
        $this->model->addHtmlAttribute("placeholder", $placeholderText);
    }

    /**
     * Returns a label that the hosting view can use in the HTML output.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->model->label != "") {
            return $this->model->label;
        }

        return StringTools::wordifyStringByUpperCase($this->getName());
    }
}
