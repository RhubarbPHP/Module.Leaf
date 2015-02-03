<?php

namespace Rhubarb\Leaf\Presenters\Application\Search;

use Rhubarb\Crown\Context;
use Rhubarb\Stem\Filters\Contains;
use Rhubarb\Stem\Filters\Equals;
use Rhubarb\Stem\Filters\Group;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;
use Rhubarb\Leaf\Views\UnitTestView;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class SearchPanelTest extends CoreTestCase
{
	/**
	 * @var UnitTestSearchPanel
	 */
	private $_panel;

	/**
	 * @var UnitTestView
	 */
	private $_view;

	protected function setUp()
	{
		parent::setUp();

		$this->_view = new UnitTestView();

		$this->_panel = new UnitTestSearchPanel();
		$this->_panel->attachMockView( $this->_view );
	}

	public function testSearchTriggersEvent()
	{
		$triggered = false;

		$this->_panel->attachEventHandler( "Search", function() use(&$triggered)
		{
			$triggered = true;
		});

		$this->_view->simulateEvent( "Search" );

		// If the search event was triggered from the view then the presenter should emit a similar event
		$this->assertTrue( $triggered );
	}

	public function testSearchControlValues()
	{
		$search = new UnitTestSearchPanel();

		// This will simulate the textbox getting a value
		$request = \Rhubarb\Crown\Context::CurrentRequest();
		$request->Post( "_Phrase", "abc123" );

		$context = new Context();
		$context->Request = $request;

		$search->generateResponse( $request );

		$values = $search->getSearchControlValues();

		$this->assertEquals( "abc123", $values[ "Phrase" ] );

		$this->assertArrayNotHasKey( "PresenterName", $values );

		$search->setSearchControlValues( [ "Phrase" => "123456" ] );

		$search->phraseTextBox->AttachMockView( $textBoxView = new UnitTestSearchPanelTextBoxView() );

		$search->generateResponse( new WebRequest() );

		$this->assertEquals( "123456", $textBoxView->GetText() );
	}

	public function testDefaultControlValuesAreUsed()
	{
		$search = new UnitTestSearchPanel();
		$values = $search->getSearchControlValues();

		$this->assertEquals( "This is the default value", $values[ "Phrase" ] );

		$search->setSearchControlValues( [ "Phrase" => "Dogs" ] );
		$search->setSearchControlValues( [ "Goats" => "Boats" ] );

		$values = $search->getSearchControlValues();

		$this->assertEquals( "This is the default value", $values[ "Phrase" ] );
	}

	public function testConfigureFiltersEventIsHandled()
	{
		$this->_panel->Phrase = "test";

		$filter = null;

		$result = $this->_panel->TestConfigureFilters( $filter );
		$this->assertInstanceOf( "Rhubarb\Stem\Filters\Group", $result );
		$filters = $result->GetFilters();
		$this->assertCount( 1, $filters );
		$this->assertInstanceOf( "Rhubarb\Stem\Filters\Contains", $filters[0] );

		$filter = new Equals( "CompanyID", "1" );

		$result = $this->_panel->TestConfigureFilters( $filter );
		$this->assertInstanceOf( "Rhubarb\Stem\Filters\Group", $result );
		$filters = $result->GetFilters();
		$this->assertCount( 2, $filters );
		$this->assertInstanceOf( "Rhubarb\Stem\Filters\Equals", $filters[0] );
		$this->assertInstanceOf( "Rhubarb\Stem\Filters\Group", $filters[1] );

		$filters = $filters[1]->GetFilters();
		$this->assertCount( 1, $filters );
		$this->assertInstanceOf( "Rhubarb\Stem\Filters\Contains", $filters[0] );

		$this->_panel->Phrase = "";

		$filter = new Equals( "CompanyID", "1" );

		$result = $this->_panel->TestConfigureFilters( $filter );
		$this->assertFalse( $result, "The panel should not want to filter as phrase is blank. False should indicate this." );
	}
}

class UnitTestSearchPanelTextBoxView extends TextBoxView
{
	public function GetText()
	{
		return $this->text;
	}
}

class UnitTestSearchPanel extends SearchPanel
{
	public $phraseTextBox;

	protected function getDefaultControlValues()
	{
		return [ "Phrase" => "This is the default value" ];
	}

	protected function createSearchControls()
	{
		return array( $this->phraseTextBox = new TextBox( "Phrase" ), new TextBox( "Goats" ) );
	}

	public function populateFilterGroup( Group $filterGroup = null )
	{
		if ( $this->Phrase )
		{
			$filterGroup->AddFilters(
				new Contains( "Surname", $this->Phrase )
			);
		}
	}

	public function TestConfigureFilters( $filterGroup )
	{
		return $this->onConfigureFilters( $filterGroup );
	}
}