<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Controls\CompositeControlPresenter;

class TestCompositePresenter extends CompositeControlPresenter
{
    protected function createView()
    {
        return new TestCompositeView();
    }
}