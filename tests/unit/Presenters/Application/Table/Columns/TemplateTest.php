<?php

namespace Rhubarb\Leaf\Tests\Presenters\ Application\Table\Columns;

use Rhubarb\Leaf\Presenters\Application\Table\Columns\Template;
use Rhubarb\Stem\Tests\Fixtures\Example;
use Rhubarb\Stem\Tests\Fixtures\ModelUnitTestCase;

class TemplateTest extends ModelUnitTestCase
{
    public function testReturnsCellValue()
    {
        $model = new Example();
        $model->Forename = "Goats Boats";

        $template = new Template("This is a template", "");

        $this->assertEquals("This is a template", $template->getCellValue($model, null));

        $template = new Template("Dear {Forename}", "");

        $this->assertEquals("Dear Goats Boats", $template->getCellValue($model, null));
    }
}