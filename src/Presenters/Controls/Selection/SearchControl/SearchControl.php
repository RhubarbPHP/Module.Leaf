<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\Presenters\Controls\Selection\SearchControl;

require_once __DIR__."/../SelectionControlPresenter.php";

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

	protected function isValueSelectable( $value )
	{
		// Search controls are often bound to int columns where the default value will be zero.
		// This should not be considered a selected item.
		if ( $value === "0" || $value === 0 )
		{
			return false;
		}

		return parent::isValueSelectable( $value );
	}

	protected function initialiseModel()
	{
		parent::initialiseModel();

		$this->model->ResultsWidth = "match";
		$this->model->AutoSubmitSearch = true;
		$this->ResultColumns = $this->getResultColumns();
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
	public function setResultsWidth( $width )
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
			return $this->getCurrentlyAvailableSelectionItems();
		});

		$this->view->attachEventHandler( "ItemSelected", function( $selectedId )
		{
			$this->SelectedItems = [ $selectedId ];

			return $selectedId;
		});
	}

	protected abstract function getResultColumns();

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