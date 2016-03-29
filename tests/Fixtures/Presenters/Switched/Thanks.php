<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\TestView;

class Thanks extends Presenter
{
    protected function createView()
    {
        $this->registerView(new TestView());
    }
}
