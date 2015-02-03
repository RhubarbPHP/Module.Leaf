<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters;

use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class TestCompositeView extends ControlView
{
	public function createPresenters()
	{
		$this->addPresenters(
			new TextBox( "Forename", 20 ),
			new TextBox( "Surname", 20 )
		);

		parent::createPresenters();
	}

	protected function printViewContent()
	{
		print $this->presenters[ "Forename" ]." ".$this->presenters[ "Surname" ];
	}
}