<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds;

use Rhubarb\Leaf\Presenters\Presenter;

class CrudsDetailsPresenter extends Presenter
{
    protected function createView()
    {
        return new CrudsDetailsView();
    }
}