<?php

namespace Rhubarb\Leaf\Leaves;

use Rhubarb\Crown\Events\Event;

trait BindableLeafTrait
{
    /**
     * @var Event
     */
    public $bindingValueChangedEvent;

    public function getBindingValueChangedEvent()
    {
        if ($this->bindingValueChangedEvent == null){
            $this->bindingValueChangedEvent = new Event();
        }
        
        return $this->bindingValueChangedEvent;
    }
}