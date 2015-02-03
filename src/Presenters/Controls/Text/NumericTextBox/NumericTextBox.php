<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\NumericTextBox;

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

/** 
 * 
 *
 * @package Rhubarb\Leaf\Presenters\Controls\Text\NumericTextBox
 * @author      acuthbert
 * @copyright   2013 GCD Technologies Ltd.
 */
class NumericTextBox extends TextBox
{
	private $_decimalPlaces = 2;

	public function __construct( $name = "", $size = 15 )
	{
		parent::__construct( $name, $size );
	}

	protected function ExtractBoundData()
	{
		return $this->model->Text;
	}

	protected function applyModelToView()
	{
		parent::applyModelToView();

		$number = (float) $this->model->Text;

		$this->view->SetText( number_format( $number, $this->_decimalPlaces, '.', '' ) );
	}
}