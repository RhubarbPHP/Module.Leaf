<?php

namespace Rhubarb\Leaf\Tests\Presenters\Dialogs;

use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Dialogs\DialogPresenter;

class DialogPresenterTest extends RhubarbTestCase
{
    public function testNameGetsDefaultOfClassName()
    {
        $dialog = new UnitTestDialogPresenter();

        $this->assertEquals("UnitTestDialog", $dialog->getName());
    }
}

class UnitTestDialogPresenter extends DialogPresenter
{
}
