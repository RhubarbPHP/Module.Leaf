<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\Password;

require_once __DIR__."/../TextBox/TextBox.class.php";

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2013
 */
class Password extends TextBox
{
	protected function createView()
	{
		$view = new TextBoxView( "password" );

		$this->RegisterView( $view );
	}
}