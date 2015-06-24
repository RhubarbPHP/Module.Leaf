<?php

namespace Rhubarb\Leaf\Tests\Validation;

use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Validation\ClientSideValidation;
use Rhubarb\Leaf\Validation\EqualToClientSide;
use Rhubarb\Stem\Models\Validation\EqualTo;

class ClientSideValidationTest extends RhubarbTestCase
{
    public function testConversionFromModelToClientSide()
    {
        $equals = new EqualTo("Test", "Value");

        $clientEquals = ClientSideValidation::fromModelValidation($equals);

        $this->assertInstanceOf(EqualToClientSide::class, $clientEquals);
    }
}
