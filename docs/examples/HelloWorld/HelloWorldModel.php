<?php

namespace Rhubarb\Leaf\Examples\HelloWorld;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\LeafModel;

class HelloWorldModel extends LeafModel
{
    /**
     * Who are we saying hello to?
     *
     * @var string
     */
    public $name = "Andrew";

    /**
     * @var Event
     */
    public $nameChangedEvent;

    public function __construct()
    {
        parent::__construct();

        // Create the name changed event object.
        $this->nameChangedEvent = new Event();
    }
}