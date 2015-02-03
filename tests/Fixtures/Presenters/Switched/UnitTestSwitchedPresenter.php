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

	protected function GetSwitchedPresenters()
	{
		return [
			"Details" => "\Rhubarb\Leaf\UnitTesting\Presenters\Switched\Details",
			"Address" => "\Rhubarb\Leaf\UnitTesting\Presenters\Switched\Address",
			"Thanks" => "\Rhubarb\Leaf\UnitTesting\Presenters\Switched\Thanks"
		];
	}

	private $details;

	protected function OnPresenterAdded( \Rhubarb\Leaf\Presenters\Presenter $presenter )
	{
		parent::OnPresenterAdded( $presenter );

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
		$this->ChangePresenter( $presenterName );
	}

	public function TestGetDefaultPresenterName()
	{
		return $this->GetDefaultPresenterName();
	}

	public function TestGetCurrentPresenterName()
	{
		return $this->GetCurrentPresenterName();
	}

	protected function initialiseModel()
	{
		parent::initialiseModel();

		$this->model->Forename = "John";
	}
}
