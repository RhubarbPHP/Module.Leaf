<?php

namespace Rhubarb\Leaf\Views;

use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Crown\UnitTesting\CoreTestCase;
use string;

class MessageViewTraitTest extends CoreTestCase
{
	public function testMessageTraitShowsMessage()
	{
		$presenter = new UnitTestingMessagePresenter();
		$view = new UnitTestingMessageView();
		$presenter->attachMockView( $view );

		$output = $presenter->test();

		$this->assertContains( "Normal content", $output );

		$view->simulateEvent( "ActivateMessage", "Sent" );

		$output = $presenter->test();

		$this->assertContains( "Message was sent", $output );

		$view->simulateEvent( "ActivateMessage", "Failed" );

		$output = $presenter->test();

		$this->assertContains( "The message failed", $output );
	}
}

class UnitTestingMessagePresenter extends Presenter
{
	protected function createView()
	{
		return new UnitTestingMessageView();
	}

	private $_message = false;

	protected function configureView()
	{
		parent::configureView();

		$this->view->attachEventHandler( "ActivateMessage", function( $message )
		{
			$this->_message = $message;
		});
	}

	protected function applyModelToView()
	{
		parent::applyModelToView();

		$this->view->message = $this->_message;
	}
}

class UnitTestingMessageView extends UnitTestView
{
	use MessageViewTrait;

	/**
	 * Should return an array of key value pairs storing message texts against an arbitrary tracking code.
	 *
	 * @return string[]
	 */
	protected function getMessages()
	{
		return
		[
			"Sent" => "Message was sent",
			"Failed" => function(){ return "The message failed"; }
		];
	}

	protected function printViewContent()
	{
		print "Normal content";
	}
}