<?php

namespace Rhubarb\Leaf\Views\Validation;

use Rhubarb\Stem\Models\Validation\HasValue;
use Rhubarb\Stem\Models\Validation\Validator;
use Rhubarb\Leaf\Presenters\Simple;
use Rhubarb\Leaf\Views\UnitTestView;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class PlaceholderTest extends CoreTestCase
{
	public function testEmptyPlaceholder()
	{
		$mvp = new Simple();
		$view = new PlaceholderTestView();
		$mvp->attachMockView( $view );

		$placeholder = new Placeholder( "Forename", $view );

		$this->assertEquals( "<em class=\"validation-placeholder\" name=\"ValidationPlaceHolder-Forename\"></em>", (string) $placeholder );
	}

	/*
	 * Suspended while validation is in flux.
	public function testErrorPlaceholder()
	{
		$mvp = new Simple();
		$view = new PlaceholderTestView();

		$mvp->AttachMockView( $view );

		$validator = new Validator();
		$validator->validations[] = new HasValue( "Forename" );

		$mvp->Validate( $validator );

		$placeholder = new Placeholder( "Forename", $view );

		$this->assertEquals( "<em class=\"validation-placeholder\" name=\"ValidationPlaceHolder-Forename\">Forename must have a value</em>", (string) $placeholder );
	}
	*/
}

class PlaceholderTestView extends UnitTestView
{
	public function SetText( $text )
	{

	}
}