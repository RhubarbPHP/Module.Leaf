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
		$this->_requiresContainer = $requireContainer;
		$this->_requiresStateInputs = $requireState;
	}

	public function TestRaiseEventOnViewBridge()
	{
		$this->RaiseEventOnViewBridge( "TestEvent", 123, 234 );
	}

	public function printViewContent()
	{
		print "Dummy Output";
	}
}
