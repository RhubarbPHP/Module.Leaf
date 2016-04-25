<?php

namespace Rhubarb\Leaf\Tests\Presenters\ Application\Table\Columns;

use Rhubarb\Leaf\Presenters\Application\Table\Columns\PresenterColumn;
use Rhubarb\Leaf\Presenters\Application\Table\Table;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Views\View;
use Rhubarb\Stem\Tests\Fixtures\Example;
use Rhubarb\Stem\Tests\Fixtures\ModelUnitTestCase;

class PresenterColumnTest extends ModelUnitTestCase
{
    public function testColumnPresents()
    {
        Example::clearObjectCache();

        $example = new Example();
        $example->Forename = "Andrew";
        $example->save();

        $example = new Example();
        $example->Forename = "Bobby";
        $example->save();

        $host = new HostPresenter();
        $output = $host->generateResponse();

        $this->assertContains("id=\"_Forename(1)\"", $output);
        $this->assertContains("value=\"Andrew\"", $output);
    }
}

class HostPresenter extends Presenter
{
    protected function createView()
    {
        return new HostView();
    }
}

class HostView extends View
{
    public $table;
    public $presented;

    public function createPresenters()
    {
        $this->addPresenters(
            $this->table = new Table(Example::find()),
            $this->presented = new TextBox("Forename")
        );

        $this->table->Columns =
            [
                "ContactID",
                new PresenterColumn($this->presented, "Forename")
            ];
    }

    protected function printViewContent()
    {
        print $this->table;
    }
}
