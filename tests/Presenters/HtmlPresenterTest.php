<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Validation\ValidatorClientSide;
use Rhubarb\Stem\Models\Validation\HasValue;
use Rhubarb\Stem\Models\Validation\Validator;

class HtmlPresenterTest extends RhubarbTestCase
{
    public function testClientSideValidationCreatedFromModelValidation()
    {
        $presenter = new UnitTestHtmlPresenter();
        $clientSideValidation = $presenter->PublicCreateDefaultClientSideValidator();

        $this->assertInstanceOf(ValidatorClientSide::class, $clientSideValidation);
        $this->assertCount(2, $clientSideValidation->validations);

        $presenter->testInvalidTypes = true;

        $clientSideValidation = $presenter->PublicCreateDefaultClientSideValidator();

        $this->assertNull($clientSideValidation);
    }
}

class UnitTestHtmlPresenter extends HtmlPresenter
{
    public function PublicCreateDefaultClientSideValidator()
    {
        return $this->createDefaultClientSideValidator();
    }

    public $testInvalidTypes = false;

    protected function createDefaultValidator()
    {
        if ($this->testInvalidTypes) {
            return new \stdClass();
        }

        $validator = new Validator();
        $validator->validations[] = new HasValue("Email");
        $validator->validations[] = new HasValue("Name");

        return $validator;
    }
}
