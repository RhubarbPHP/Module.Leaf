<?php

namespace Rhubarb\Leaf\Tests\Presenters\Controls;

use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;
use Rhubarb\Leaf\Presenters\Controls\ControlView;

class ControlViewTest extends RhubarbTestCase
{
    public function testCssClass()
    {
        $mockView = new ControlMockView();

        $controlPresenter = new ControlPresenter();
        $controlPresenter->CssClassNames = ["billy-goat", "chicken"];
        $controlPresenter->attachMockView($mockView);
        $controlPresenter->generateResponse();

        $this->assertEquals(" class=\"billy-goat chicken\"", $mockView->PublicGetClassTag());
    }
}

class UnitTestControl extends ControlPresenter
{
}

class ControlMockView extends ControlView
{
    public function PublicGetClassTag()
    {
        return $this->getClassTag();
    }
}