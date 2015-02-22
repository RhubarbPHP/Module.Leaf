<?php

namespace Gcd\Tests;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class ViewTest extends \Rhubarb\Crown\UnitTesting\CoreTestCase
{
	public function testAddPresenterRaisesEvent()
	{
		$addedPresenter = null;

		$view = new \Rhubarb\Leaf\Views\SimpleView();
		$view->attachEventHandler( "OnPresenterAdded", function( $presenter ) use ( &$addedPresenter )
		{
			$addedPresenter = $presenter;
		} );

		$view->AddPresenters( new \Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox( "TestBox" ) );

		$this->assertNotNull( $addedPresenter );
		$this->assertInstanceOf( "\Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox", $addedPresenter );

	}
}
