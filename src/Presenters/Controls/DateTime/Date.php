<?php

namespace Rhubarb\Leaf\Presenters\Controls\DateTime;

require_once __DIR__."/../Text/TextBox/TextBox.class.php";

use Rhubarb\Crown\DateTime\CoreDate;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class Date extends TextBox
{
	public function __construct( $name = "", $defaultValue = null )
	{
		parent::__construct( $name );

		$this->SetSize( 10 );

		$this->attachClientSidePresenterBridge = true;

		$this->_defaultValue = $defaultValue;
	}

	protected function applyModelToView()
	{
		if ( $this->_defaultValue !== null && $this->model->Text == "" )
		{
			$this->model->Text = date( "d/m/Y", strtotime( $this->_defaultValue ) );
		}

		parent::applyModelToView();
	}

	protected function ApplyBoundData( $data )
	{
		$time = false;

		try
		{
			$time = new CoreDate( $data );
		}
		catch( \Exception $er )
		{

		}

		if ( $time === false )
		{
			$this->model->Text = "";
		}
		else
		{
			$this->model->Text = $time->format( "d/m/Y" );
		}
	}

	protected function ExtractBoundData()
	{
		if ( preg_match( '|(\d{1,2})/(\d{1,2})/(\d{2,4})|', $this->model->Text, $match ) )
		{
			return $match[3]."-".$match[2]."-".$match[1];
		}

		return "";
	}

	protected function createView()
	{
		return new DateView();
	}
}