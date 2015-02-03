<?php

namespace Rhubarb\Leaf\Presenters\Controls\CheckBoxes;

require_once __DIR__."/../ControlPresenter.class.php";

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class CheckBox extends ControlPresenter
{
	protected function createView()
	{
		return new CheckBoxView();
	}

	protected function parseRequestForCommand()
	{
		if ( $_SERVER[ "REQUEST_METHOD" ] != "POST" )
		{
			return;
		}

		$request = $request = \Rhubarb\Crown\Context::CurrentRequest();
		$values = $request->Post( $this->getIndexedPresenterPath() );

		if( is_array( $values ) )
		{
			foreach( $values as $index => $value )
			{
				$this->_viewIndex = str_replace( "_", "", $index );
				$this->model->Value = $value;
				$this->SetBoundData();
			}
		}
		else
		{
			if( $values !== null )
			{
				$this->model->Value = $values;
				$this->SetBoundData();
			}
			else
			{
				$this->model->Value = 0;
				$this->SetBoundData();
			}
		}
	}

	protected function applyModelToView()
	{
		parent::applyModelToView();

		$this->view->SetCheckedStatus( (bool) $this->Value );
	}
}