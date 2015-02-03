<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters;

use Rhubarb\Leaf\Presenters\Forms\Form;

class TestViewIndexPresenter extends Form
{
	protected function CreateView()
	{
		return new TestViewIndexView();
	}

} 