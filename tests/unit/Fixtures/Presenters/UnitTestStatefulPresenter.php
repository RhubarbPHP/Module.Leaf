<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\PresenterModel;

class UnitTestStatefulPresenter extends Presenter
{
    protected function createView()
    {
        $this->registerView(new TestView());
    }

    /**
     * The overriding class should implement to return a model class that extends PresenterModel
     *
     * This is normally done with an anonymous class for convenience
     *
     * @return PresenterModel
     */
    protected function createModel()
    {
        return new UnitTestStatefulModel();
    }
}
