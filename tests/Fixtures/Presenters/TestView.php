<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters;
use Rhubarb\Leaf\Views\HtmlView;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class TestView extends HtmlView
{
	public function __construct( $requireContainer = true, $requireState = true )
	{
		$this->requiresContainer = $requireContainer;
		$this->requiresStateInputs = $requireState;
	}

	public function TestRaiseEventOnViewBridge()
	{
		$this->raiseEventOnViewBridge( "TestEvent", 123, 234 );
	}

	public function printViewContent()
	{
		print "Dummy Output";
	}
}
