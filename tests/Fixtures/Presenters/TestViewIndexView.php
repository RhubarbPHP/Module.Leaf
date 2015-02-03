<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters;

use Rhubarb\Leaf\Views\HtmlView;

class TestViewIndexView extends HtmlView
{
	public function createPresenters()
	{
		parent::createPresenters();

		$this->addPresenters(
			new TestCompositePresenter( "Test" )
		);
	}

	protected function printViewContent()
	{
		$this->presenters[ "Test" ]->displayWithIndex(0);
		$this->presenters[ "Test" ]->displayWithIndex(1);
	}
}