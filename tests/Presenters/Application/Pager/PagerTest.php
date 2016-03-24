<?php

namespace Rhubarb\Leaf\Tests\Presenters\ Application\Pager;

use Rhubarb\Crown\Context;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Exceptions\PagerOutOfBoundsException;
use Rhubarb\Leaf\Presenters\Application\Pager\Pager;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\UnitTestView;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Tests\unit\Fixtures\ModelUnitTestCase;

class PagerTest extends ModelUnitTestCase
{
    private $collection;
    /**
     * @var Pager
     */
    private $pager;

    /**
     * @var TestPagerView
     */
    private $mock;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        for ($x = 0; $x < 500; $x++) {
            $user = new User();
            $user->Username = $x;
            $user->Save();
        }
    }

    protected function setUp()
    {
        parent::setUp();

        $this->CreateMocks();
    }

    private function CreateMocks()
    {
        $this->collection = new Collection(User::class);

        $this->mock = new TestPagerView();

        $this->pager = new Pager($this->collection, 50);
        $this->pager->attachMockView($this->mock);
    }

    public function testPagesCalculatedCorrectly()
    {
        $this->pager->generateResponse();

        $this->assertEquals(10, $this->mock->numberOfPages);
        $this->assertEquals(1, $this->mock->pageNumber);
        $this->assertEquals(50, $this->mock->numberPerPage);

        $this->pager->setNumberPerPage(30);
        $this->pager->generateResponse();

        $this->assertEquals(17, $this->mock->numberOfPages);
        $this->assertEquals(1, $this->mock->pageNumber);
        $this->assertEquals(30, $this->mock->numberPerPage);
    }

    public function testPageNumberCanBeChanged()
    {
        $this->mock->simulateEvent("PageChanged", 2);
        $this->pager->generateResponse();
        $this->assertEquals(2, $this->mock->pageNumber);

        $this->collection->rewind();

        $user = $this->collection->current();

        $this->assertEquals(50, $user->Username);
    }

    public function testPagerStaysInBounds()
    {
        $thrown = false;

        try {
            $this->pager->setPageNumber(11);
        } catch (PagerOutOfBoundsException $er) {
            $thrown = true;
        }

        $this->assertTrue($thrown);
        $this->assertEquals(1, $this->pager->PageNumber);
    }

    public function testPagerPicksUpOnHttpGetPageNumbers()
    {
        $request = new WebRequest();

        $context = new Context();
        $context->Request = $request;

        $request->Request($this->pager->PresenterPath . "-page", 3);

        $this->CreateMocks();

        $this->pager->test();

        $this->assertEquals(3, $this->mock->pageNumber);
    }
}

class TestPagerView extends UnitTestView
{
    public $numberOfPages;
    public $pageNumber;
    public $numberPerPage;

    public function SetNumberOfPages($numberOfPages)
    {
        $this->numberOfPages = $numberOfPages;
    }

    public function SetPageNumber($pageNumber)
    {
        $this->pageNumber = $pageNumber;
    }

    public function SetNumberPerPage($numberPerPage)
    {
        $this->numberPerPage = $numberPerPage;
    }
}
