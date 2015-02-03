<?php

namespace Rhubarb\Leaf\Presenters\Controls\DateTime;

use Rhubarb\Crown\DateTime\CoreTime;
use Rhubarb\Leaf\Presenters\Controls\CompositeControlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;

class Time extends CompositeControlPresenter
{
	private $_defaultValue = null;
	private $_minuteInterval;
	private $_hourStart;
	private $_hourEnd;

	public function __construct( $name = "", $defaultValue = null, $minuteInterval = 1, $hourStart = 0, $hourEnd = 23 )
	{
		parent::__construct( $name );
		$this->_defaultValue = $defaultValue;
		$this->_minuteInterval = $minuteInterval;
		$this->_hourStart = $hourStart;
		$this->_hourEnd = $hourEnd;
	}

	protected function applyModelToView()
	{
		if( $this->_defaultValue !== null && $this->model->Hours == "" && $this->model->Minutes == "" )
		{
			$this->model->Hours = $this->_defaultValue->format( "H" );
			$this->model->Minutes = $this->_defaultValue->format( "i" );
		}

		parent::applyModelToView();
	}

	protected function ApplyBoundData( $data )
	{
		$time = false;

		try
		{
			$time = new CoreTime( $data );
		}
		catch( \Exception $er )
		{
		}

		if( $time === false )
		{
			$this->model->Hours = "";
			$this->model->Minutes = "";
		}
		else
		{
			$this->model->Hours = $time->format( "H" );
			$this->model->Minutes = $time->format( "i" );
		}
	}

	protected function ExtractBoundData()
	{
		$hours = (int) $this->model->Hours;
		$minutes = (int) $this->model->Minutes;

		$time = new CoreTime();
		$time->setTime( $hours, $minutes );

		return $time;
	}

	protected function createView()
	{
		return new TimeView( $this->_minuteInterval, $this->_hourStart, $this->_hourEnd );
	}
}