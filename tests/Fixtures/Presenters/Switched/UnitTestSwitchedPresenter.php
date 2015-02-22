<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Switched;

use Rhubarb\Leaf\Presenters\ModelProvider;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class UnitTestSwitchedPresenter extends \Rhubarb\Leaf\Presenters\SwitchedPresenter
{
	use ModelProvider;

	protected function getSwitchedPresenters()
	{
		return [
			"Details" => "\Rhubarb\Leaf\UnitTesting\Presenters\Switched\Details",
			"Address" => "\Rhubarb\Leaf\UnitTesting\Presenters\Switched\Address",
			"Thanks" => "\Rhubarb\Leaf\UnitTesting\Presenters\Switched\Thanks"
		];
	}

	private $details;

	protected function onPresenterAdded( \Rhubarb\Leaf\Presenters\Presenter $presenter )
	{
		parent::onPresenterAdded( $presenter );

		if ( is_a( $presenter, "\Rhubarb\Leaf\UnitTesting\Presenters\Switched\Details" ) )
		{
			$this->details = $presenter;
		}
	}

	public function GetDetailsPresenter()
	{
		return $this->details;
	}

	public function TestPresenterIsChanged( $presenterName )
	{
		$this->changePresenter( $presenterName );
	}

	public function TestGetDefaultPresenterName()
	{
		return $this->getDefaultPresenterName();
	}

	public function TestGetCurrentPresenterName()
	{
		return $this->getCurrentPresenterName();
	}

	protected function initialiseModel()
	{
		parent::initialiseModel();

		$this->model->Forename = "John";
	}
}
