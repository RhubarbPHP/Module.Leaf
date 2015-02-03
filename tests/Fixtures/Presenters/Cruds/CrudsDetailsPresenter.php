<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Cruds;

use Rhubarb\Leaf\Presenters\Presenter;

class CrudsDetailsPresenter extends Presenter
{
	protected function createView()
	{
		return new CrudsDetailsView();
	}

}