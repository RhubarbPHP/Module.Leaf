<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Presenters\ModelProvider;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\SwitchedPresenter;

class UnitTestSwitchedPresenter extends SwitchedPresenter
{
    use ModelProvider;

    protected function getSwitchedPresenters()
    {
        return [
            "Details" => Details::class,
            "Address" => Address::class,
            "Thanks" => Thanks::class
        ];
    }

    private $details;

    protected function onPresenterAdded(Presenter $presenter)
    {
        parent::onPresenterAdded($presenter);

        if (is_a($presenter, Details::class)) {
            $this->details = $presenter;
        }
    }

    public function GetDetailsPresenter()
    {
        return $this->details;
    }

    public function TestPresenterIsChanged($presenterName)
    {
        $this->changePresenter($presenterName);
    }

    public function TestGetDefaultPresenterName()
    {
        return $this->getDefaultPresenterName();
    }

    public function TestGetCurrentPresenterName()
    {
        return $this->getCurrentPresenter();
    }

    protected function initialiseModel()
    {
        parent::initialiseModel();

        $this->model->Forename = "John";
    }
}
