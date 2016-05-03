<?php

namespace Rhubarb\Leaf\Leaves;

use Rhubarb\Crown\Events\Event;

trait BindableLeafTrait
{
    /**
     * @var Event
     */
    public $bindingValueChangedEvent;

    /**
     * @var Event
     */
    public $bindingValueRequestedEvent;

    public function getBindingValueChangedEvent()
    {
        if ($this->bindingValueChangedEvent == null){
            $this->bindingValueChangedEvent = new Event();
        }
        
        return $this->bindingValueChangedEvent;
    }

    public function getBindingValueRequestedEvent()
    {
        if ($this->bindingValueRequestedEvent == null){
            $this->bindingValueRequestedEvent = new Event();
        }

        return $this->bindingValueRequestedEvent;
    }
}