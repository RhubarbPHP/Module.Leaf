<?php

namespace Rhubarb\Leaf\Validation;

use Gcd\Core\UnitTesting\CoreTestCase;
use Rhubarb\Stem\Models\Validation\EqualTo;

class ClientSideValidationTest extends CoreTestCase
{
    public function testConversionFromModelToClientSide()
    {
        $equals = new EqualTo("Test", "Value");

        $clientEquals = ClientSideValidation::fromModelValidation($equals);

        $this->assertInstanceOf("Rhubarb\Leaf\Validation\EqualToClientSide", $clientEquals);
    }
}
