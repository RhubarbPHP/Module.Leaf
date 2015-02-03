<?php

namespace Rhubarb\Leaf\Views;

/** 
 * Provides basic support for receiving errors from a presenter.
 *
 * @package Rhubarb\Leaf\Views
 * @author      acuthbert
 * @copyright   2014 GCD Technologies Ltd.
 */
trait WithErrors
{
	protected $_errors = [];

	public function AddError( $error )
	{
		$this->_errors[] = $error;
	}

	public function GetErrors()
	{
		return $this->_errors;
	}
} 