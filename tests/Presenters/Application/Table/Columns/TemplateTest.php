<?php

namespace Rhubarb\Leaf\Presenters\Application\Table\Columns;

use Rhubarb\Stem\UnitTesting\Example;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class TemplateTest extends CoreTestCase
{
	public function testReturnsCellValue()
	{
		$model = new Example();
		$model->Forename = "Goats Boats";

		$template = new Template( "This is a template", "" );

		$this->assertEquals( "This is a template", $template->getCellValue( $model, null ) );

		$template = new Template( "Dear {Forename}", "" );

		$this->assertEquals( "Dear Goats Boats", $template->getCellValue( $model, null ) );
	}
}