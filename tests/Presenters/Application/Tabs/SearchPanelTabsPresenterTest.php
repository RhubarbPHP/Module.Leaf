<?php

namespace Rhubarb\Leaf\Presenters\Application\Tabs;

require_once( __DIR__."/../Search/SearchPanelTest.php" );

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Application\Search\UnitTestSearchPanel;
use Rhubarb\Leaf\Views\UnitTestView;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class SearchPanelTabsPresenterTest extends CoreTestCase
{
	public function testInflationSupportsArray()
	{
		$tabs = new UnitTestSearchPanelTabsPresenter();

		$tabs->setTabDefinitions(
			[
				"Big" => [ "Phrase" => "This is a big phrase" ]
			]
		);

		$searchResults = new SearchResultsTabDefinition( "Search Results", [] );
		$searchResults->selected = true;

		$this->assertEquals(
			[ new SearchPanelTabDefinition( "Big", [ "Phrase" => "This is a big phrase" ] ),
			  $searchResults
			],
			$tabs->PublicInflateTabDefinitions()
		);
	}

	public function testSelectingTabChangesSearchPanelControlValues()
	{
		$panel = new UnitTestSearchPanel();
		$tabs = new UnitTestSearchPanelTabsPresenter();

		$tabs->setTabDefinitions(
			[
				"Big" => new SearchPanelTabDefinition( "Big", [ "Phrase" => "This is a big phrase" ] ),
				"Small" => new SearchPanelTabDefinition( "Small", [ "Phrase" => "Small Phrase" ] )
			]
		);

		$tabs->bindEventsWith( $panel );

		// Let's simulate going big.
		$tabs->selectTabByIndex( 0 );
		$tabs->test();

		$this->assertEquals( "This is a big phrase", $panel->model->Phrase );

		// Let's simulate going small.
		$tabs->selectTabByIndex( 1 );
		$tabs->test();

		$this->assertEquals( "Small Phrase", $panel->model->Phrase );
	}

	public function testSearchResultsTabShows()
	{
		$panel = new UnitTestSearchPanel();
		$tabs = new UnitTestSearchPanelTabsPresenter();

		$tabs->setTabDefinitions(
			[
				"Big" => new SearchPanelTabDefinition( "Big", [ "Phrase" => "This is a big phrase" ] ),
				"Small" => new SearchPanelTabDefinition( "Small", [ "Phrase" => "Small Phrase" ] )
			]
		);

		$capturedTabDefinitions = [];

		$mockView = new UnitTestView();
		$mockView->attachMethod( "SetTabDefinitions", function( $tabDefinitions ) use ( &$capturedTabDefinitions )
		{
			$capturedTabDefinitions = $tabDefinitions;
		});

		$tabs->attachMockView( $mockView );

		$tabs->bindEventsWith( $panel );

		$panel->model->Phrase = "A different Phrase";

		$tabs->generateResponse( new WebRequest() );

		$this->assertEquals( "Search Results", $capturedTabDefinitions[ sizeof( $capturedTabDefinitions ) - 1 ]->label );
	}
}

class UnitTestSearchPanelTabsPresenter extends SearchPanelTabsPresenter
{
	public function PublicInflateTabDefinitions()
	{
		$tabs = $this->inflateTabDefinitions();
		$this->markSelectedTab( $tabs );

		return $tabs;
	}
}
