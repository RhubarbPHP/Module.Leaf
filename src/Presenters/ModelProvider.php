<?php

namespace Rhubarb\Leaf\Presenters;

/**
 * Replaces the default bubbling up behaviour of the GetBoundData and SetBoundData events by
 * actually handling the binding calls.
 *
 * This trait should be applied to the host presenter to make sure sub presenters are able to bind to it.
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
trait ModelProvider
{
	protected $_modelProvider = true;

	/**
	 * Updates the model with data bound to a sub presenter.
	 *
	 * @param string $dataKey
	 * @param $data
	 * @param bool $viewIndex
	 */
	protected function SetDataFromPresenter( $dataKey, $data, $viewIndex = false )
	{
		$this->SetData( $dataKey, $data, $viewIndex );

		$this->OnModelUpdatedFromSubPresenter();
	}

	protected function SetData( $dataKey, $data, $viewIndex = false )
	{
		if ( $viewIndex !== false && $viewIndex !== "" )
		{
			if ( !isset( $this->model[ $dataKey ] ) )
			{
				$this->model[ $dataKey ] = [];
			}

			$modelData = $this->model[ $dataKey ];

			if ( !is_array( $modelData ) )
			{
				$modelData = [ $modelData ];
			}

			$modelData[ $viewIndex ] = $data;

			$this->model[ $dataKey ] = $modelData;
		}
		else
		{
			$this->model[ $dataKey ] = $data;
		}
	}

	protected function OnModelUpdatedFromSubPresenter()
	{

	}

	/**
	 * Provides model data to the requesting presenter.
	 *
	 * @param string $dataKey
	 * @param bool $viewIndex
	 * @return null
	 */
	protected function GetDataForPresenter( $dataKey, $viewIndex = false )
	{
		return $this->GetData( $dataKey, $viewIndex );
	}

	protected function GetData( $dataKey, $viewIndex = false )
	{
		if( !isset( $this->model[ $dataKey ] ) )
		{
			return $this->RaiseEvent( "GetData", $dataKey, $viewIndex );
		}

		if( $viewIndex !== "" && $viewIndex !== false )
		{
			if( isset( $this->model[ $dataKey ][ $viewIndex ] ) )
			{
				return $this->model[ $dataKey ][ $viewIndex ];
			}
		}
		else
		{
			return $this->model[ $dataKey ];
		}

		return null;
	}

	protected function GetModel()
	{
		return $this->model;
	}
}
