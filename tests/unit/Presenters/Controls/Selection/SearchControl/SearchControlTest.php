<?php

namespace Rhubarb\Leaf\Tests\Presenters\Controls\Selection\SearchControl;

use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Controls\Selection\SearchControl\SearchControl;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\UnitTestView;

class SearchControlTest extends RhubarbTestCase
{
    public function testSearchControlSearchPressedEventReturnsItems()
    {
        $view = new UnitTestView();

        $testSearch = new UnitTestSearchControl();
        $testSearch->attachMockView($view);

        $items = $testSearch->simulateSearchPressed("test");

        $this->assertEquals("test", $testSearch->Phrase);
        $this->assertEquals("b", $items[1]->label);
        $this->assertEquals("c", $items[2]->label);
        $this->assertEquals(0, $items[0]->value);
    }

    public function testItemCanBeSelected()
    {
        $view = new UnitTestView();

        $testSearch = new UnitTestSearchControl();
        $testSearch->attachMockView($view);

        $response = $testSearch->simulateItemSelected(4);

        $this->assertEquals(true, $response);
        $this->assertEquals(4, $testSearch->SelectedItems[0]);
    }
}

class UnitTestSearchControl extends SearchControl
{
    public function simulateItemSelected($item)
    {
        return $this->view->raiseEvent("ItemSelected", $item);
    }

    public function simulateSearchPressed($phrase)
    {
        $this->setSelectionItems(
            [
                [0, "a"],
                [1, "b"],
                [2, "c"]
            ]);

        return $this->view->raiseEvent("SearchPressed", $phrase);
    }

    protected function getResultColumns()
    {
        return [];
    }
}
