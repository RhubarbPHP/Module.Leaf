<?php

namespace Rhubarb\Leaf\Presenters\Controls;

use Rhubarb\Crown\UnitTesting\CoreTestCase;

class ControlViewTest extends CoreTestCase
{
	public function testCssClass()
	{
		$mockView = new ControlMockView();

		$controlPresenter = new ControlPresenter();
		$controlPresenter->CssClassNames = [ "billy-goat", "chicken" ];
		$controlPresenter->AttachMockView( $mockView );
		$controlPresenter->GenerateResponse();

		$this->assertEquals( " class=\"billy-goat chicken\"", $mockView->PublicGetClassTag() );
	}
}

class UnitTestControl extends ControlPresenter
{

}

class ControlMockView extends ControlView
{
	public function PublicGetClassTag()
	{
		return $this->GetClassTag();
	}
}