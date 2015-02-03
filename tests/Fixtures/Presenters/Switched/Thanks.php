<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Switched;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class Thanks extends \Rhubarb\Leaf\Presenters\Presenter
{
	protected function createView()
	{
		$this->RegisterView( new \Rhubarb\Leaf\UnitTesting\Presenters\TestView() );
	}
}
