<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters;

use Rhubarb\Leaf\Presenters\Controls\CompositeControlPresenter;

class TestCompositePresenter extends CompositeControlPresenter
{
	protected function createView()
	{
		return new TestCompositeView();
	}
}