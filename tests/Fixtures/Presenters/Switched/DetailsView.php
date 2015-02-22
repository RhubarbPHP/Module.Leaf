<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Switched;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class DetailsView extends \Rhubarb\Leaf\Views\View
{
	public static $forename;

	public function createPresenters()
	{
		self::$forename = new UnitTestTextBox( "Forename" );

		$this->addPresenters(
			self::$forename
		);

		parent::createPresenters();
	}

	public function printViewContent()
	{
		print $this->presenters[ "Forename" ];
	}
}
