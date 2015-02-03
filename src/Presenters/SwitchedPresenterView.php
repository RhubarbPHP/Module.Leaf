<?php

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__."/../Views/View.class.php";

/**
 * This simple view presents a single sub presenter for the 'step' that should
 * current be shown.
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class SwitchedPresenterView extends \Rhubarb\Leaf\Views\View
{
	private $presenter;

	public function createPresenters()
	{
		// We need to register our sub presenter early to make sure it's included
		// in the events processing loop.
		$this->presenter = $this->RaiseEvent( "GetCurrentPresenter" );

		$this->addPresenters( $this->presenter );
	}

	public function printViewContent()
	{
		print $this->presenter;
	}
}