<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\SearchControl;

require_once __DIR__."/../SelectionControlPresenter.class.php";

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;

/**
 * A control presenter that forms the base for controls that require an event based search followed by selection.
 *
 * @property bool $AutoSubmitSearch Set to true to cause the search to happen on keypress.
 * @property int $MinimumPhraseLength If set, the number of characters required before a search can occur.
 */
abstract class SearchControl extends SelectionControlPresenter
{
	public function __construct( $name = "" )
	{
		parent::__construct( $name );

		$this->attachClientSidePresenterBridge = true;
	}

	protected function IsValueSelectable( $value )
	{
		// Search controls are often bound to int columns where the default value will be zero.
		// This should not be considered a selected item.
		if ( $value === "0" || $value === 0 )
		{
			return false;
		}

		return parent::IsValueSelectable( $value );
	}

	protected function initialiseModel()
	{
		parent::initialiseModel();

		$this->model->ResultsWidth = "match";
		$this->model->AutoSubmitSearch = true;
		$this->ResultColumns = $this->GetResultColumns();
	}

	protected function createView()
	{
		return new SearchControlView();
	}

	/**
	 * Sets the width of the results panel.
	 *
	 * This is passed verbatim to the javascript width style so you can pass "150px" and "80%".
	 *
	 * There is one special case, "match" which the javascript will understand as making the results container
	 * match the width of the search box.
	 *
	 * @param $width
	 */
	public function SetResultsWidth( $width )
	{
		$this->model->ResultsWidth = $width;
	}

	protected function configureView()
	{
		parent::configureView();

		$this->view->attachEventHandler( "SearchPressed", function( $phrase )
		{
			$this->Phrase = $phrase;

			// Note the pattern here is not to engage with the phrase directly, but purely to record it in the
			// model and let the standard method that returns items decide how to handle it.
			return $this->GetCurrentlyAvailableSelectionItems();
		});

		$this->view->attachEventHandler( "ItemSelected", function( $selectedId )
		{
			$this->SelectedItems = [ $selectedId ];

			return $selectedId;
		});
	}

	protected abstract function GetResultColumns();

	protected function getPublicModelPropertyList()
	{
		$properties = parent::getPublicModelPropertyList();
		$properties[] = "FocusOnLoad";
		$properties[] = "AutoSubmitSearch";
		$properties[] = "MinimumPhraseLength";
		$properties[] = "ResultColumns";
		$properties[] = "ResultsWidth";

		return $properties;
	}
}