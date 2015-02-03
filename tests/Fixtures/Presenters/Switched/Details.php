<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Switched;

use Rhubarb\Leaf\Presenters\Presenter;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class Details extends Presenter
{
	public static $forenameTextBound = "";

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

	protected function createView()
	{
		$this->RegisterView( new DetailsView() );
	}

	public function TestChangingPresenterThroughEvent()
	{
		$this->RaiseEvent( "ChangePresenter", "Address" );
	}
}
