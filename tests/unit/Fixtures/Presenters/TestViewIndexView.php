<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Views\View;

class TestViewIndexView extends View
{
    public function createPresenters()
    {
        parent::createPresenters();

        $this->addPresenters(
            new TestCompositePresenter("Test")
        );
    }

    protected function printViewContent()
    {
        $this->presenters["Test"]->displayWithIndex(0);
        $this->presenters["Test"]->displayWithIndex(1);
    }
}