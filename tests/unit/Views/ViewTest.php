<?php

namespace Rhubarb\Leaf\Tests\Views;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\SimpleView;
use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class ViewTest extends RhubarbTestCase
{
    public function testAddPresenterRaisesEvent()
    {
        $addedPresenter = null;

        $view = new SimpleView();
        $view->attachEventHandler("OnPresenterAdded", function ($presenter) use (&$addedPresenter) {
            $addedPresenter = $presenter;
        });

        $view->AddPresenters(new TextBox("TestBox"));

        $this->assertNotNull($addedPresenter);
        $this->assertInstanceOf(TextBox::class, $addedPresenter);
    }
}
