<?php

namespace Rhubarb\Leaf\Validation;

use Rhubarb\Stem\Models\Validation\EqualTo;
use Gcd\Core\UnitTesting\CoreTestCase;

class ClientSideValidationTest extends CoreTestCase
{
	public function testConversionFromModelToClientSide()
	{
		$equals = new EqualTo( "Test", "Value" );

		$clientEquals = ClientSideValidation::fromModelValidation( $equals );

		$this->assertInstanceOf( "Rhubarb\Leaf\Validation\EqualToClientSide", $clientEquals );
	}
}
