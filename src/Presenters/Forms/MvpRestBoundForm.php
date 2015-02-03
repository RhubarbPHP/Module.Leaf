<?php

namespace Rhubarb\Leaf\Presenters\Forms;

require_once __DIR__."/Form.class.php";

/**
 * Provides an automatic way for a form to get the model object provided by the MvpRestHandler
 *
 * Also replaces data bindings so that the rest supplied model is bound to instead.
 *
 */
use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\CreatePresentersFromSchemaTrait;

abstract class MvpRestBoundForm extends Form
{
	use CreatePresentersFromSchemaTrait;

	/**
	 * The Model supplied by the RestHandler
	 *
	 * @var \Rhubarb\Stem\Models\Model
	 */
	protected $_restModel;

	/**
	 * The Collection supplied by the RestHandler
	 *
	 * @var \Rhubarb\Stem\Collections\Collection
	 */
	protected $_restCollection;

	public function SetRestModel( $restModel )
	{
		$this->_restModel = $restModel;

		// Add the unique identifier to the presenter model. This allows validation, which only uses the
		// presenter model to access the rest model unique identifier for doing duplication checks for example.
		$this->model->RestModelUniqueIdentifier = $restModel->UniqueIdentifier;
	}

	public function GetRestModel()
	{
		return $this->_restModel;
	}

	public function IsConfigured()
	{
		// As the rest model data is sucked into the form's model, this stops the configured state
		// being flagged.
		return false;
	}

	public function GetXmlRpcUrl()
	{
		$request = Context::CurrentRequest();
		return $request->UrlPath;
	}

	public function SetRestCollection( $restCollection )
	{
		$this->_restCollection = $restCollection;
	}

	public function GetRestCollection()
	{
		return $this->_restCollection;
	}

	/**
	 * Updates the model with data bound to a sub presenter.
	 *
	 * @param string $dataKey
	 * @param $data
	 */
	protected function SetDataFromPresenter( $dataKey, $data, $viewIndex = false )
	{
		$this->SetData( $dataKey, $data, $viewIndex );
	}

	protected function SetData( $dataKey, $data, $viewIndex = false )
	{
		if ( $viewIndex )
		{
			if ( $this->_restModel )
			{
				if ( !isset( $this->_restModel[ $dataKey ] ) )
				{
					$this->_restModel[ $dataKey ] = [];
				}

				$existingData = $this->_restModel[ $dataKey ];
				$existingData[ $viewIndex ] = $data;

				$this->_restModel[ $dataKey ] = $existingData;
			}

			if ( !isset( $this->model[ $dataKey ] ) )
			{
				$this->model[ $dataKey ] = [];
			}

			$existingData = $this->model[ $dataKey ];
			$existingData[ $viewIndex ] = $data;

			$this->model[ $dataKey ] = $existingData;
		}
		else
		{
			if ( $this->_restModel )
			{
				$this->_restModel[ $dataKey ] = $data;
			}

			$this->model[ $dataKey ] = $data;
		}
	}

	/**
	 * Provides model data to the requesting presenter.
	 *
	 * @param string $dataKey
	 * @param bool $viewIndex
	 * @return null
	 */
	protected function getDataForPresenter( $dataKey, $viewIndex = false )
	{
		return $this->getData( $dataKey, $viewIndex );
	}

	protected function getData( $dataKey, $viewIndex = false )
	{
		if( isset( $this->model[ $dataKey ] ) )
		{
			return $this->model[ $dataKey ];
		}

		if( isset( $this->_restModel[ $dataKey ] ) )
		{
			return $this->_restModel[ $dataKey ];
		}

		return null;
	}

	protected function GetModel()
	{
		return $this->_restModel;
	}
}