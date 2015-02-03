<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\DropDown;

require_once __DIR__."/../SelectionControlPresenter.class.php";

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlPresenter;

class DropDown extends SelectionControlPresenter
{
	protected function createView()
	{
		return new DropDownView();
	}
}