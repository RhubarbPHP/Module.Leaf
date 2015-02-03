<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\SearchControl;

use Rhubarb\Leaf\Views\UnitTestView;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class SearchControlTest extends CoreTestCase
{
	public function testSearchControlSearchPressedEventReturnsItems()
	{
		$view = new UnitTestView();

		$testSearch = new UnitTestSearchControl();
		$testSearch->AttachMockView( $view );

		$items = $testSearch->SimulateSearchPressed( "test" );

		$this->assertEquals( "test", $testSearch->Phrase );
		$this->assertEquals( "b", $items[1]->label );
		$this->assertEquals( "c", $items[2]->label );
		$this->assertEquals( 0, $items[0]->value );
	}

	public function testItemCanBeSelected()
	{
		$view = new UnitTestView();

		$testSearch = new UnitTestSearchControl();
		$testSearch->AttachMockView( $view );

		$response = $testSearch->SimulateItemSelected( 4 );

		$this->assertEquals( true, $response );
		$this->assertEquals( 4, $testSearch->SelectedItems[0] );
	}
}

class UnitTestSearchControl extends SearchControl
{
	public function SimulateItemSelected( $item )
	{
		return $this->view->RaiseEvent( "ItemSelected", $item );
	}

	public function SimulateSearchPressed( $phrase )
	{
        $this->SetSelectionItems(
        [
            [ 0, "a" ],
            [ 1, "b" ],
            [ 2, "c" ]
        ]);

		return $this->view->RaiseEvent( "SearchPressed", $phrase );
	}

    protected function GetResultColumns()
    {
        return [];
    }
}
