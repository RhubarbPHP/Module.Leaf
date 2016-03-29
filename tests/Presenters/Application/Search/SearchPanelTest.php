<?php

namespace Rhubarb\Leaf\Tests\Presenters\ Application\Search;

use Rhubarb\Crown\Context;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Application\Search\SearchPanel;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\UnitTestView;
use Rhubarb\Stem\Filters\Contains;
use Rhubarb\Stem\Filters\Equals;
use Rhubarb\Stem\Filters\Group;

class SearchPanelTest extends RhubarbTestCase
{
    /**
     * @var UnitTestSearchPanel
     */
    private $panel;

    /**
     * @var UnitTestView
     */
    private $view;

    protected function setUp()
    {
        parent::setUp();

        $this->view = new UnitTestView();

        $this->panel = new UnitTestSearchPanel();
        $this->panel->attachMockView($this->view);
    }

    public function testSearchTriggersEvent()
    {
        $triggered = false;

        $this->panel->attachEventHandler("Search", function () use (&$triggered) {
            $triggered = true;
        });

        $this->view->simulateEvent("Search");

        // If the search event was triggered from the view then the presenter should emit a similar event
        $this->assertTrue($triggered);
    }

    public function testSearchControlValues()
    {
        $search = new UnitTestSearchPanel();

        // This will simulate the textbox getting a value
        $request = Context::currentRequest();
        $request->post("_Phrase", "abc123");

        $context = new Context();
        $context->Request = $request;

        $search->generateResponse($request);

        $values = $search->getSearchControlValues();

        $this->assertEquals("abc123", $values["Phrase"]);

        $this->assertArrayNotHasKey("PresenterName", $values);

        $search->setSearchControlValues(["Phrase" => "123456"]);

        $search->phraseTextBox->attachMockView($textBoxView = new UnitTestSearchPanelTextBoxView());

        $search->generateResponse(new WebRequest());

        $this->assertEquals("123456", $textBoxView->getText());
    }

    public function testDefaultControlValuesAreUsed()
    {
        $search = new UnitTestSearchPanel();
        $values = $search->getSearchControlValues();

        $this->assertEquals("This is the default value", $values["Phrase"]);

        $search->setSearchControlValues(["Phrase" => "Dogs"]);
        $search->setSearchControlValues(["Goats" => "Boats"]);

        $values = $search->getSearchControlValues();

        $this->assertEquals("This is the default value", $values["Phrase"]);
    }

    public function testConfigureFiltersEventIsHandled()
    {
        $this->panel->Phrase = "test";

        $filter = null;

        $result = $this->panel->testConfigureFilters($filter);
        $this->assertInstanceOf(Group::class, $result);
        $filters = $result->getFilters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(Contains::class, $filters[0]);

        $filter = new Equals("CompanyID", "1");

        $result = $this->panel->testConfigureFilters($filter);
        $this->assertInstanceOf(Group::class, $result);
        $filters = $result->getFilters();
        $this->assertCount(2, $filters);
        $this->assertInstanceOf(Equals::class, $filters[0]);
        $this->assertInstanceOf(Group::class, $filters[1]);

        $filters = $filters[1]->getFilters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(Contains::class, $filters[0]);

        $this->panel->Phrase = "";

        $filter = new Equals("CompanyID", "1");

        $result = $this->panel->testConfigureFilters($filter);
        $this->assertFalse($result, "The panel should not want to filter as phrase is blank. False should indicate this.");
    }
}

class UnitTestSearchPanelTextBoxView extends TextBoxView
{
    public function getText()
    {
        return $this->text;
    }
}

class UnitTestSearchPanel extends SearchPanel
{
    public $phraseTextBox;

    protected function getDefaultControlValues()
    {
        return ["Phrase" => "This is the default value"];
    }

    protected function createSearchControls()
    {
        return [$this->phraseTextBox = new TextBox("Phrase"), new TextBox("Goats")];
    }

    public function populateFilterGroup(Group $filterGroup = null)
    {
        if ($this->Phrase) {
            $filterGroup->addFilters(
                new Contains("Surname", $this->Phrase)
            );
        }
    }

    public function testConfigureFilters($filterGroup)
    {
        return $this->onConfigureFilters($filterGroup);
    }
}
