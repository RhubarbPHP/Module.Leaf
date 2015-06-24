<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Presenter;

class UnitTestStatefulPresenter extends Presenter
{
    protected function createView()
    {
        $this->registerView(new TestView());
    }
}
