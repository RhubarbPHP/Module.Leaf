<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\Hidden;

require_once __DIR__."/../TextBox/TextBox.class.php";

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;

/**
 * @author    acuthbert
 * @copyright GCD Technologies 2013
 */
class Hidden extends TextBox
{
	protected function CreateView()
	{
		$view = new TextBoxView( "hidden" );

		$this->RegisterView( $view );
	}
}