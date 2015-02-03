<?php

namespace Rhubarb\Leaf\Views\Validation;

use Rhubarb\Leaf\Views\View;

class Placeholder
{
	private $_validationName;
	private $_hostingView;

	public function __construct( $validationName, View $hostingView )
	{
		$this->_validationName = $validationName;
		$this->_hostingView = $hostingView;
	}

	public function __toString()
	{
		$errors = $this->_hostingView->GetValidationErrors( $this->_validationName );
		$errorMessages = [];

		$errorHtml = "";

		foreach( $errors as $error )
		{
			$errorMessages[] = $error->message;
		}

		if ( count( $errorMessages ) > 0 )
		{
			$errorHtml = implode( "<br/>", $errorMessages );
		}

		return "<em class=\"validation-placeholder\" name=\"ValidationPlaceHolder-".$this->_validationName."\">".$errorHtml."</em>";
	}
}