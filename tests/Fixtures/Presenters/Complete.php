<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters;

/**
 * A second step of a multi step test.
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class Complete extends \Rhubarb\Leaf\Presenters\Presenter
{
	protected function createView()
	{
		$this->RegisterView( new CompleteView() );
	}
}
