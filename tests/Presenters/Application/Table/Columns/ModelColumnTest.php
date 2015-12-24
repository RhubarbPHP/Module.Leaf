<?php

namespace Rhubarb\Leaf\Tests\Presenters\Application\Table\Columns;

use Rhubarb\Leaf\Presenters\Application\Table\Columns\DateColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\ModelColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Columns\SortableColumn;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\MySqlDateColumn;
use Rhubarb\Stem\Schema\Columns\StringColumn;
use Rhubarb\Stem\Tests\unit\Fixtures\Example;
use Rhubarb\Stem\Tests\unit\Fixtures\ModelUnitTestCase;

class ModelColumnTest extends ModelUnitTestCase
{
    public function testLabelIsSetAutomatically()
    {
        $tableColumn = new ModelColumn("BezierCurve", "My Label");

        $this->assertEquals("My Label", $tableColumn->label);

        $tableColumn = new ModelColumn("BezierCurve", "");

        $this->assertEquals("BezierCurve", $tableColumn->label);
    }

    public function testReturnsCellValue()
    {
        $modelColumn = new ModelColumn("Forename", "");

        $model = new Example();
        $model->Forename = "BillyBob";

        $this->assertEquals("BillyBob", $modelColumn->getCellContent($model, null));
    }

    public function testCanCreateAppropriateType()
    {
        $stringColumn = new StringColumn("Forename", 50);
        $dateColumn = new MySqlDateColumn("DateOfBirth");

        $tableColumn = ModelColumn::createTableColumnForSchemaColumn($stringColumn, "Test");
        $this->assertInstanceOf(ModelColumn::class, $tableColumn);

        $tableColumn = ModelColumn::createTableColumnForSchemaColumn($dateColumn, "Test");
        $this->assertInstanceOf(DateColumn::class, $tableColumn);
    }

    public function testIsSortable()
    {
        $stringColumn = new StringColumn("Forename", 50);

        $tableColumn = ModelColumn::createTableColumnForSchemaColumn($stringColumn, "Test");

        $this->assertInstanceOf(SortableColumn::class, $tableColumn);
        $this->assertEquals("Forename", $tableColumn->getSortableColumnName());
    }
}