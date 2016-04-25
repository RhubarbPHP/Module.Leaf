<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Forms\Form;

class TestViewIndexPresenter extends Form
{
    protected function createView()
    {
        return new TestViewIndexView();
    }
}