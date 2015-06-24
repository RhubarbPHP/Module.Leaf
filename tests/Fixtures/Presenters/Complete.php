<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Presenter;

/**
 * A second step of a multi step test.
 */
class Complete extends Presenter
{
    protected function createView()
    {
        $this->registerView(new CompleteView());
    }
}
