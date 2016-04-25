<?php

namespace Rhubarb\Leaf\Tests\Presenters;
use Rhubarb\Leaf\Exceptions\InvalidPresenterNameException;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\UnitTestSwitchedPresenter;
use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;

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
        $this->assertEquals("Details", $this->host->TestGetDefaultPresenterName());
    }

    public function testCurrentPresenterNameIsTheDefault()
    {
        $this->assertEquals("Details", $this->host->TestGetCurrentPresenterName());

        $this->host->model->CurrentPresenterName = "Address";
        $this->assertEquals("Address", $this->host->TestGetCurrentPresenterName());
    }

    public function testChangePresenterManually()
    {
        $this->assertEquals("Details", $this->host->TestGetCurrentPresenterName());

        $threwRequiresException = false;

        try {
            $this->host->TestPresenterIsChanged("Address");
        } catch (RequiresViewReconfigurationException $er) {
            $threwRequiresException = true;
        }

        $this->assertTrue($threwRequiresException, "ChangePresenter() must throw RequiresRegenerationException");

        $this->assertEquals("Address", $this->host->TestGetCurrentPresenterName());

        $this->setExpectedException(InvalidPresenterNameException::class);

        $this->host->TestPresenterIsChanged("NonExistantPresenter");
    }

    public function testChangePresenterThroughEvent()
    {
        $this->assertEquals("Details", $this->host->TestGetCurrentPresenterName());

        $presenter = $this->host->GetDetailsPresenter();
        $threwRequiresException = false;

        try {
            $presenter->TestChangingPresenterThroughEvent();
        } catch (RequiresViewReconfigurationException $er) {
            $threwRequiresException = true;
        }

        $this->assertTrue($threwRequiresException, "ChangePresenter() must throw RequiresRegenerationException");
        $this->assertEquals("Address", $this->host->TestGetCurrentPresenterName());
    }
}
