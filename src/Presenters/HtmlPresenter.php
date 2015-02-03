<?php

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__."/Presenter.class.php";

use Rhubarb\Crown\ClientSide\Validation\ClientSideValidation;
use Rhubarb\Crown\ClientSide\Validation\ValidatorClientSide;
use Rhubarb\Stem\Models\Validation\Validation;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2013
 */
class HtmlPresenter extends Presenter
{
	/**
	 * Returns by default the server side validator (which should be created using
	 * the appropriate ClientSide variants.
	 *
	 * Override to provide different validation on the client side. Ignore completely if you aren't using
	 * the default validation behaviours.
	 *
	 * @return \Rhubarb\Stem\Models\Validation\Validator
	 */
	protected function CreateDefaultClientSideValidator()
	{
		$validation = $this->CreateDefaultValidator();

		if ( !$validation instanceof Validation )
		{
			return null;
		}

		if ( ( $validation instanceof Validation ) && !( in_array( "Rhubarb\Crown\ClientSide\Validation\ClientSideValidation", ValidatorClientSide::NestedClassUses( $validation ) ) ) )
		{
			// Convert the validation to a client side validation if required. If the validation doesn't have a
			// matching client side version, null will returned essentially disabling the client side validation.
			$validation = ClientSideValidation::FromModelValidation( $validation );
		}

		return $validation;
	}

	protected function OnViewRegistered()
	{
		parent::OnViewRegistered();

		$this->view->attachEventHandler( "GetDefaultClientSideValidator", function()
		{
			return $this->CreateDefaultClientSideValidator();
		});
	}

}