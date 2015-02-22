<?php
/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2013
 */

namespace Rhubarb\Leaf\Presenters\Application\Table\Columns;


use Rhubarb\Stem\Repositories\MySql\Schema\Columns\Date;
use Rhubarb\Stem\Schema\Columns\Column;
use Rhubarb\Stem\Schema\Columns\String;
use Rhubarb\Stem\UnitTesting\Example;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class ModelColumnTest extends CoreTestCase
{
	public function testLabelIsSetAutomatically()
	{
		$tableColumn = new ModelColumn( "BezierCurve", "My Label" );

		$this->assertEquals( "My Label", $tableColumn->label );

		$tableColumn = new ModelColumn( "BezierCurve", "" );

		$this->assertEquals( "BezierCurve", $tableColumn->label );
	}

	public function testReturnsCellValue()
	{
		$modelColumn = new ModelColumn( "Forename", "" );

		$model = new Example();
		$model->Forename = "BillyBob";

		$this->assertEquals( "BillyBob", $modelColumn->getCellContent( $model, null ) );
	}

	public function testCanCreateAppropriateType()
	{
		$stringColumn = new String( "Forename", 50 );
		$dateColumn = new Date( "DateOfBirth" );

		$tableColumn = ModelColumn::createTableColumnForSchemaColumn( $stringColumn, "Test" );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\ModelColumn", $tableColumn );

		$tableColumn = ModelColumn::createTableColumnForSchemaColumn( $dateColumn, "Test" );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\DateColumn", $tableColumn );
	}

	public function testIsSortable()
	{
		$stringColumn = new String( "Forename", 50 );

		$tableColumn = ModelColumn::createTableColumnForSchemaColumn( $stringColumn, "Test" );

		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\ISortableColumn", $tableColumn );
		$this->assertEquals( "Forename", $tableColumn->getSortableColumnName() );
	}
}