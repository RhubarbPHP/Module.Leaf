<?php

namespace Rhubarb\Leaf\Leaves;

use Rhubarb\Crown\Events\Event;

interface BindableLeafInterface
{
    public function getValue();

    /**
     * @return Event
     */
    public function getBindingValueChangedEvent();

    /**
     * @return Event
     */
    public function getBindingValueRequestedEvent();
}