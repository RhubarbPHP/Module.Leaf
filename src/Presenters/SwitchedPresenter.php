<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Leaf\Exceptions\InvalidPresenterNameException;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;

require_once __DIR__."/Presenter.class.php";

/**
 * The switched presenter hosts a number of sub presenters and manages selection of the appropriate presenter.
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class SwitchedPresenter extends Presenter
{
	/**
	 * A collection of presenter names and class names to use.
	 * @var array
	 */
	private $_switchedPresenters = array();

	/**
	 * Override this to return a mapping of presenter names to classes.
	 *
	 * @see SwitchedPresenter::_switchedPresenters;
	 */
	protected function GetSwitchedPresenters()
	{
		return [];
	}

	protected function ChangePresenter( $newPresenterName )
	{
		if ( !isset( $this->_switchedPresenters[ $newPresenterName ] ) )
		{
			throw new InvalidPresenterNameException( $newPresenterName );
		}

		$this->model->CurrentPresenterName = $newPresenterName;

		// We throw this exception to signal that the processing pipeline should reinitialise
		// the presenter. Of course this needs done as the hosted presenter should now be a
		// different one.

		throw new RequiresViewReconfigurationException();
	}

	protected function createView()
	{
		$this->_switchedPresenters = $this->GetSwitchedPresenters();

		$this->RegisterView( new SwitchedPresenterView() );
	}

	protected function configureView()
	{
		$this->view->attachEventHandler( "GetCurrentPresenter", function()
		{
			$class = $this->_switchedPresenters[ $this->GetCurrentPresenterName() ];

			$object = new $class();

			return $object;
		} );

		parent::configureView();
	}

	/**
	 * Gets the currently active presenter name.
	 */
	public function GetCurrentPresenterName()
	{
		if ( !isset( $this->model->CurrentPresenterName ) )
		{
			return $this->GetDefaultPresenterName();
		}

		return $this->model->CurrentPresenterName;
	}

	/**
	 * Returns the name of the default presenter name.
	 *
	 * The default implementation simply returns the first from the collection.
	 *
	 * @return string
	 */
	protected function GetDefaultPresenterName()
	{
		reset( $this->_switchedPresenters );

		return key( $this->_switchedPresenters );
	}

	protected function OnPresenterAdded( Presenter $presenter )
	{
		// Registers with the view's sub presenter to make sure that we get notified
		// when the presenter should change.
		$presenter->attachEventHandler( "ChangePresenter", function( $presenterName )
		{
			$this->ChangePresenter( $presenterName );
		});
	}
}
