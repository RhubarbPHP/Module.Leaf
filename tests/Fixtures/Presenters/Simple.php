<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Leaf\Presenters\Presenter;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class Simple extends Presenter
{
	public function __construct()
	{
		parent::__construct( "Simple" );

		$this->attachEventHandler( "FirstEvent", function()
		{
			$this->lastEventProcessed = "FirstEvent";
		} );

		$this->attachEventHandler( "SecondEvent", function()
		{
			$this->lastEventProcessed = "SecondEvent";
		} );
	}

	public function RemoveEventHandlers()
	{
		$this->ClearEventHandlers();
	}

	protected function getPublicModelPropertyList()
	{
		$properties = parent::getPublicModelPropertyList();
		$properties[] = "ModelSetting";

		return $properties;
	}

	public $supportsLatePresenterRegistration = false;

	protected function SupportsLateSubPresenterRegistration()
	{
		return $this->supportsLatePresenterRegistration;
	}

	protected function parseRequestForCommand()
	{
		parent::parseRequestForCommand();

		// Fire two events for our unit test.
		$this->RaiseDelayedEvent( "FirstEvent" );
		$this->RaiseEvent( "SecondEvent" );
	}

	/**
	 * Examined by unit test.
	 *
	 * @var string
	 */
	public $lastEventProcessed = "";

	protected function createView()
	{
		return new \Rhubarb\Leaf\Views\SimpleView();
	}

	public function GetSubPresenters()
	{
		return $this->subPresenters;
	}

	protected function configureView()
	{
		parent::configureView();

		$this->view->attachEventHandler( "Save", function()
		{
			$this->Save();
		} );

		$this->view->SetText( "Don't change this content - it should match the unit test." );
	}

	protected function Save()
	{

	}

	protected function CommandUpdateText( $text = "The text has changed!" )
	{
		$this->view->SetText( $text );
	}
}
