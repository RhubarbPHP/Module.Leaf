<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\TestView;

class Address extends Presenter
{
    protected function createView()
    {
        $this->registerView(new TestView());
    }

    public $restModel;
    public $restCollection;

    public function setRestModel($restObject)
    {
        $this->restModel = $restObject;
    }

    public function setRestCollection($restCollection)
    {
        $this->restCollection = $restCollection;
    }
}
