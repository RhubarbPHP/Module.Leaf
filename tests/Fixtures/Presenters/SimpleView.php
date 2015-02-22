<?php

namespace Rhubarb\Leaf\Views;

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class SimpleView extends View implements ISimpleView
{
	private $text;

	public function PrintViewContent()
	{
		print $this->text;
	}

	public function SetText( $text )
	{
		$this->text = $text;
	}

	public function CreatePresenters()
	{
		parent::CreatePresenters();

		$this->AddPresenters(
			[
			"ForenameA" => new TextBox( "Forename" ),
			"ForenameB" => new TextBox( "Forename" )
			]
		);
	}

}
