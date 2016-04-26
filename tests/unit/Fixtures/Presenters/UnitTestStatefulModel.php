<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\PresenterModel;

class UnitTestStatefulModel extends PresenterModel
{
    public $testValue;

    protected function getExposableModelProperties()
    {
        $list = parent::getExposableModelProperties();
        $list[] = "testValue";

        return $list;
    }
}