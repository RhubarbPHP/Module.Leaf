<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Presenters\ModelProvider;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\SwitchedPresenter;

class UnitTestSwitchedPresenter extends SwitchedPresenter
{
    protected function createSwitchedPresenters()
    {
        return [
            "Details" => new Details(),
            "Address" => new Address(),
            "Thanks" => new Thanks()
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

    public function getDetailsPresenter()
    {
        return $this->details;
    }

    public function testPresenterIsChanged($presenterName)
    {
        $this->changePresenter($presenterName);
    }

    public function testGetDefaultPresenterName()
    {
        return $this->getDefaultPresenterName();
    }

    public function testGetCurrentPresenterName()
    {
        return $this->getCurrentPresenter();
    }

    protected function initialiseModel()
    {
        parent::initialiseModel();

        $this->model->Forename = "John";
    }
}
