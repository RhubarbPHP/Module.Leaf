<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Switched;

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class UnitTestTextBox extends TextBox
{
	protected function applyModelToView()
	{
		parent::applyModelToView();

		self::$textBoxValue = $this->model->Text;
	}

	public static $textBoxValue = "";
}
