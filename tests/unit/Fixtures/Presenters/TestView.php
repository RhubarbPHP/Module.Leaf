<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Views\View;

class TestView extends View
{
    public function __construct($requireContainer = true, $requireState = true)
    {
        parent::__construct();
        
        $this->requiresContainer = $requireContainer;
        $this->requiresStateInputs = $requireState;
    }

    public function printViewContent()
    {
        print "Dummy Output";
    }
}
