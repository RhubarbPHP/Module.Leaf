<?php

namespace Rhubarb\Leaf\Views;

use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Presenters\PresenterModel;
use Rhubarb\Leaf\UnitTesting\Presenters\TestView;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class HtmlViewTest extends CoreTestCase
{
	public function testWrappers()
	{
		$presenter = new TestPresenter( "Forename", true, true );
		$output = $presenter->generateResponse();

		// Careful now! The format of this string is important - dont' be tidying it up!
		$this->assertEquals( '<div id="Forename" class="TestView" presenter-name="Forename">
Dummy Output
<input type="hidden" name="ForenameState" id="ForenameState" value="{&quot;PresenterName&quot;:&quot;Forename&quot;,&quot;PresenterPath&quot;:&quot;Forename&quot;}" />
</div>', $output );
	}

	public function testRaisingEventOnViewBridge()
	{
		$presenter = new TestPresenter( "Forename", true, true );
		$presenter->test();

		$_SERVER[ 'HTTP_X_REQUESTED_WITH' ] = 'xmlhttprequest';

		$view = $presenter->testView;
		$view->TestRaiseEventOnViewBridge();
		$response = $presenter->generateResponse( new WebRequest() );

		$content = $response->GetContent();

		$this->assertContains( '<event name="TestEvent" target="Forename"><param><![CDATA[123]]></param><param><![CDATA[234]]></param></event>', $content );

		unset( $_SERVER[ 'HTTP_X_REQUESTED_WITH' ] );
	}
}

class TestPresenter extends Presenter
{
	private $_requiresContainer = true;
	private $_requiresStateInputs = true;
	public $testView;

	public function __construct( $name = "", $requireContainer = true, $requireState = true )
	{
		parent::__construct( $name );

		$this->_requiresContainer = $requireContainer;
		$this->_requiresStateInputs = $requireState;
	}

	protected function createView()
	{
		$this->testView = new TestView( $this->_requiresContainer, $this->_requiresStateInputs );
		$this->registerView( $this->testView );
	}
}