<?php

namespace Rhubarb\Leaf\Tests\Presenters\ Application\Table;

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\BooleanColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\DateColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\ModelColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\OneToOneRelationshipColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\Template;
use Rhubarb\Leaf\Presenters\Application\Table\Table;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\UnitTestView;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Tests\Fixtures\Example;
use Rhubarb\Stem\Tests\Fixtures\ModelUnitTestCase;

class TableTest extends ModelUnitTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $example = new Example();
        $example->Forename = "Andrew";
        $example->Surname = "Cuthbert";
        $example->save();
    }

    public function testTableDefaultToEmptyCollection()
    {
        $table = new Table();

        $this->assertNull($table->getCollection());
    }

    public function testTableSetsCollection()
    {
        $table = new Table();

        $list = new Collection(Example::class);
        $table->setCollection($list);

        $this->assertEquals($list, $table->getCollection());
    }

    public function testTableInterpretsColumnsArray()
    {
        $list = new Collection(Example::class);
        $table = new Table($list);
        $mockView = new MockTableView();

        $table->attachMockView($mockView);

        $table->Columns = [
            "Forename",
            "Surname",
            "GoatsCheese",
            "DateOfBirth",
            "Company",
            "CompanyName",
            "CreatedDate",
            "KeyContact",
            "MyTestValue"
        ];

        $table->generateResponse();

        $this->assertCount(9, $mockView->columns);
        $this->assertInstanceOf(ModelColumn::class, $mockView->columns[0]);
        $this->assertInstanceOf(Template::class, $mockView->columns[2]);
        $this->assertInstanceOf(DateColumn::class, $mockView->columns[3]);
        $this->assertInstanceOf(OneToOneRelationshipColumn::class, $mockView->columns[4]);
        $this->assertInstanceOf(Template::class, $mockView->columns[5]);
        $this->assertInstanceOf(DateColumn::class, $mockView->columns[6]);
        $this->assertInstanceOf(BooleanColumn::class, $mockView->columns[7]);
        $this->assertInstanceOf(ModelColumn::class, $mockView->columns[8]);
        $this->assertEquals("Forename", $mockView->columns[0]->label);
        $this->assertEquals("Date Of Birth", $mockView->columns[3]->label);
        $this->assertEquals("Company", $mockView->columns[4]->label);
        $this->assertEquals("Company Name", $mockView->columns[5]->label);
    }

    public function testTableColumnSorts()
    {
        $list = new Collection(Example::class);
        $table = new Table($list);
        $mockView = new MockTableView();

        $table->attachMockView($mockView);

        $table->Columns = [
            "Forename",
            "Surname",
            "GoatsCheese",
            "DateOfBirth",
            "Company",
            "CreatedDate",
            "KeyContact"
        ];

        $table->initialise();

        $mockView->simulateEvent("ColumnClicked", 0);
        $table->generateResponse(Context::currentRequest());
        $sorts = $list->getSorts();
        $this->assertEquals(["Forename" => true], $sorts);


        $mockView->simulateEvent("ColumnClicked", 0);
        $table->generateResponse(Context::currentRequest());
        $sorts = $list->getSorts();
        $this->assertEquals(["Forename" => false], $sorts);


        $mockView->simulateEvent("ColumnClicked", 1);
        $table->generateResponse(Context::currentRequest());
        $sorts = $list->getSorts();
        $this->assertEquals(["Surname" => true], $sorts);
    }
}

class MockTableView extends UnitTestView
{
    public $columns = [];
}
