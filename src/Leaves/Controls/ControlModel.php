<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\LeafModel;

class ControlModel extends LeafModel
{
    /**
     * The controls current value
     *
     * @var string
     */
    public $value;

    /**
     * Raised when the value changes.
     *
     * @var Event
     */
    public $valueChangedEvent;

    public function __construct()
    {
        $this->valueChangedEvent = new Event();
    }

    /**
     * Should normally be called instead of setting $value directly.
     *
     * Raises the $valueChangedEvent.
     *
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->valueChangedEvent->raise();
    }
}