<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Stem\Models\Validation\HasValue;
use Rhubarb\Stem\Models\Validation\Validator;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class HtmlPresenterTest extends CoreTestCase
{
	public function testClientSideValidationCreatedFromModelValidation()
	{
		$presenter = new UnitTestHtmlPresenter();
		$clientSideValidation = $presenter->PublicCreateDefaultClientSideValidator();

		$this->assertInstanceOf( "Rhubarb\Crown\ClientSide\Validation\ValidatorClientSide", $clientSideValidation );
		$this->assertCount( 2, $clientSideValidation->validations );

		$presenter->testInvalidTypes = true;

		$clientSideValidation = $presenter->PublicCreateDefaultClientSideValidator();

		$this->assertNull( $clientSideValidation );
	}
}

class UnitTestHtmlPresenter extends HtmlPresenter
{
	public function PublicCreateDefaultClientSideValidator()
	{
		return $this->CreateDefaultClientSideValidator();
	}

	public $testInvalidTypes = false;

	protected function CreateDefaultValidator()
	{
		if ( $this->testInvalidTypes )
		{
			return new \stdClass();
		}

		$validator = new Validator();
		$validator->validations[ ] = new HasValue( "Email" );
		$validator->validations[ ] = new HasValue( "Name" );

		return $validator;
	}
}
