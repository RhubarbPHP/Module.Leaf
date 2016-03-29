<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Views\HtmlView;

class TestView extends HtmlView
{
    public function __construct($requireContainer = true, $requireState = true)
    {
        $this->requiresContainer = $requireContainer;
        $this->requiresStateInputs = $requireState;
    }

    public function testRaiseEventOnViewBridge()
    {
        $this->raiseEventOnViewBridge("TestEvent", 123, 234);
    }

    public function printViewContent()
    {
        print "Dummy Output";
    }
}
