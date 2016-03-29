<?php

namespace Rhubarb\Leaf\Tests\Presenters;

use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Exceptions\InvalidPresenterNameException;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\UnitTestSwitchedPresenter;

class SwitchedPresenterTest extends RhubarbTestCase
{
    /**
     * @var UnitTestSwitchedPresenter
     */
    private $host;

    protected function setUp()
    {
        parent::setUp();

        $this->host = new UnitTestSwitchedPresenter();
        $this->host->initialise();
    }

    public function testDefaultPresenterIsTheFirst()
    {
        $this->assertEquals("Details", $this->host->testGetDefaultPresenterName());
    }

    public function testCurrentPresenterNameIsTheDefault()
    {
        $this->assertEquals("Details", $this->host->testGetCurrentPresenterName());

        $this->host->model->CurrentPresenterName = "Address";
        $this->assertEquals("Address", $this->host->testGetCurrentPresenterName());
    }

    public function testChangePresenterManually()
    {
        $this->assertEquals("Details", $this->host->testGetCurrentPresenterName());

        $threwRequiresException = false;

        try {
            $this->host->testPresenterIsChanged("Address");
        } catch (RequiresViewReconfigurationException $er) {
            $threwRequiresException = true;
        }

        $this->assertTrue($threwRequiresException, "ChangePresenter() must throw RequiresRegenerationException");

        $this->assertEquals("Address", $this->host->testGetCurrentPresenterName());

        $this->setExpectedException(InvalidPresenterNameException::class);

        $this->host->testPresenterIsChanged("NonExistantPresenter");
    }

    public function testChangePresenterThroughEvent()
    {
        $this->assertEquals("Details", $this->host->testGetCurrentPresenterName());

        $presenter = $this->host->getDetailsPresenter();
        $threwRequiresException = false;

        try {
            $presenter->testChangingPresenterThroughEvent();
        } catch (RequiresViewReconfigurationException $er) {
            $threwRequiresException = true;
        }

        $this->assertTrue($threwRequiresException, "ChangePresenter() must throw RequiresRegenerationException");
        $this->assertEquals("Address", $this->host->testGetCurrentPresenterName());
    }
}
