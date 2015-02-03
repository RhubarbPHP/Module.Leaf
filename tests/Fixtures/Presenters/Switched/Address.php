<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Switched;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class Address extends \Rhubarb\Leaf\Presenters\Presenter
{
	protected function createView()
	{
		$this->registerView( new \Rhubarb\Leaf\UnitTesting\Presenters\TestView() );
	}

	public $restModel;
	public $restCollection;

	public function SetRestModel( $restObject )
	{
		$this->restModel = $restObject;
	}

	public function SetRestCollection( $restCollection )
	{
		$this->restCollection = $restCollection;
	}
}
