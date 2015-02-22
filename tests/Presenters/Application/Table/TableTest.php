<?php

namespace Rhubarb\Leaf\Presenters\Application\Table;

use Rhubarb\Crown\Context;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\UnitTesting\Example;
use Rhubarb\Leaf\Views\UnitTestView;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2013
 */
class TableTest extends CoreTestCase
{
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		$example = new Example();
		$example->Forename = "Andrew";
		$example->Surname = "Cuthbert";
		$example->Save();
	}

	public function testTableDefaultToEmptyCollection()
	{
		$table = new Table();

		$this->assertNull( $table->getCollection() );
	}

	public function testTableSetsCollection()
	{
		$table = new Table();

		$list = new Collection( "Rhubarb\Stem\UnitTesting\Example" );
		$table->setCollection( $list );

		$this->assertEquals( $list, $table->getCollection() );
	}

	public function testTableInterpretsColumnsArray()
	{
		$list = new Collection( "Rhubarb\Stem\UnitTesting\Example" );
		$table = new Table( $list );
		$mockView = new MockTableView();

		$table->attachMockView( $mockView );

		$table->Columns = array(
			"Forename",
			"Surname",
			"GoatsCheese",
			"DateOfBirth",
			"Company",
			"CompanyName",
			"CreatedDate",
			"KeyContact",
			"MyTestValue"
		);

		$table->generateResponse();

		$this->assertCount( 9, $mockView->columns );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\ModelColumn", $mockView->columns[0] );
		$this->assertEquals( "Forename", $mockView->columns[0]->label );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\Template", $mockView->columns[2] );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\DateColumn", $mockView->columns[3] );
		$this->assertEquals( "Date Of Birth", $mockView->columns[3]->label );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\OneToOneRelationshipColumn", $mockView->columns[4] );
		$this->assertEquals( "Company", $mockView->columns[4]->label );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\Template", $mockView->columns[5] );
		$this->assertEquals( "Company Name", $mockView->columns[5]->label );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\DateColumn", $mockView->columns[6] );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\BooleanColumn", $mockView->columns[7] );
		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Application\Table\Columns\ModelColumn", $mockView->columns[8] );
	}

	public function testTableColumnSorts()
	{
		$list = new Collection( "Rhubarb\Stem\UnitTesting\Example" );
		$table = new Table( $list );
		$mockView = new MockTableView();

		$table->attachMockView( $mockView );

		$table->Columns = array(
			"Forename",
			"Surname",
			"GoatsCheese",
			"DateOfBirth",
			"Company",
			"CreatedDate",
			"KeyContact"
		);

		$table->initialise();

		$mockView->simulateEvent( "ColumnClicked", 0 );
		$table->generateResponse( Context::CurrentRequest() );
		$sorts = $list->GetSorts();
		$this->assertEquals( [ "Forename" => true ], $sorts );


		$mockView->simulateEvent( "ColumnClicked", 0 );
		$table->generateResponse( Context::CurrentRequest() );
		$sorts = $list->GetSorts();
		$this->assertEquals( [ "Forename" => false ], $sorts );


		$mockView->simulateEvent( "ColumnClicked", 1 );
		$table->generateResponse( Context::CurrentRequest() );
		$sorts = $list->GetSorts();
		$this->assertEquals( [ "Surname" => true ], $sorts );
	}
}

class MockTableView extends UnitTestView
{
	public $columns = array();
}