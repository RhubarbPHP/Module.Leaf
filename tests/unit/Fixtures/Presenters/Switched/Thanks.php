<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Tests\Fixtures\Presenters\TestView;
use Rhubarb\Leaf\Presenters\Presenter;

class Thanks extends Presenter
{
    protected function createView()
    {
        $this->registerView(new TestView());
    }
}
