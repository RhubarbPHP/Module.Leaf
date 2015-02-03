<?php

namespace Rhubarb\Leaf\Presenters;
use Rhubarb\Leaf\UnitTesting\Presenters\TestView;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class UnitTestStatefulPresenter extends Presenter
{
	protected function createView()
	{
		$this->registerView( new TestView() );
	}
}
