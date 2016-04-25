<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Presenters\Presenter;

class Details extends Presenter
{
    public static $forenameTextBound = "";

    public $restModel;
    public $restCollection;

    public function SetRestModel($restObject)
    {
        $this->restModel = $restObject;
    }

    public function SetRestCollection($restCollection)
    {
        $this->restCollection = $restCollection;
    }

    protected function createView()
    {
        $this->registerView(new DetailsView());
    }

    public function TestChangingPresenterThroughEvent()
    {
        $this->RaiseEvent("ChangePresenter", "Address");
    }
}
