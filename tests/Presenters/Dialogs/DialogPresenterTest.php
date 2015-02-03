<?php

namespace Rhubarb\Leaf\Presenters\Dialogs;

use Rhubarb\Crown\UnitTesting\CoreTestCase;

class DialogPresenterTest extends CoreTestCase
{
	public function testNameGetsDefaultOfClassName()
	{
		$dialog = new UnitTestDialogPresenter();

		$this->assertEquals( "UnitTestDialog", $dialog->getName() );
	}
}

class UnitTestDialogPresenter extends DialogPresenter
{

}
