<?php

namespace Gcd\Tests;

use Rhubarb\Leaf\UnitTesting\Presenters\Switched\UnitTestSwitchedPresenter;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class SwitchedPresenterTest extends \Rhubarb\Crown\UnitTesting\CoreTestCase
{
	/**
	 * @var UnitTestSwitchedPresenter
	 */
	private $host;

	protected function setUp()
	{
		parent::setUp();

		$this->host = new UnitTestSwitchedPresenter();
		$this->host->Initialise();
	}

	public function testDefaultPresenterIsTheFirst()
	{
		$this->assertEquals( "Details", $this->host->TestGetDefaultPresenterName() );
	}

	public function testCurrentPresenterNameIsTheDefault()
	{
		$this->assertEquals( "Details", $this->host->TestGetCurrentPresenterName() );

		$this->host->model->CurrentPresenterName = "Address";
		$this->assertEquals( "Address", $this->host->TestGetCurrentPresenterName() );
	}

	public function testChangePresenterManually()
	{
		$this->assertEquals( "Details", $this->host->TestGetCurrentPresenterName() );

		$threwRequiresException = false;

		try
		{
			$this->host->TestPresenterIsChanged( "Address" );
		}
		catch( \Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException $er )
		{
			$threwRequiresException = true;
		}

		$this->assertTrue( $threwRequiresException, "ChangePresenter() must throw RequiresRegenerationException" );

		$this->assertEquals( "Address", $this->host->TestGetCurrentPresenterName() );

		$this->setExpectedException( "\Rhubarb\Leaf\Exceptions\InvalidPresenterNameException" );

		$this->host->TestPresenterIsChanged( "NonExistantPresenter" );
	}

	public function testChangePresenterThroughEvent()
	{
		$this->assertEquals( "Details", $this->host->TestGetCurrentPresenterName() );

		$presenter = $this->host->GetDetailsPresenter();
		$threwRequiresException = false;

		try
		{
			$presenter->TestChangingPresenterThroughEvent();
		}
		catch( \Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException $er )
		{
			$threwRequiresException = true;
		}

		$this->assertTrue( $threwRequiresException, "ChangePresenter() must throw RequiresRegenerationException" );
		$this->assertEquals( "Address", $this->host->TestGetCurrentPresenterName() );

	}
}
