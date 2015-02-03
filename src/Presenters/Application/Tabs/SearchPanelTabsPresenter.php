<?php

namespace Rhubarb\Leaf\Presenters\Application\Tabs;

use Rhubarb\Leaf\Presenters\Application\Search\SearchPanel;
use Rhubarb\Leaf\Presenters\Presenter;

class SearchPanelTabsPresenter extends TabsPresenter
{
	protected function OnSelectedTabChanged( $tabIndex )
	{
		parent::OnSelectedTabChanged( $tabIndex );

		// If the tab that's been selected has control values attached, throw an event to say so.
		$tab = $this->GetTabByIndex( $tabIndex );

		if ( $tab instanceof SearchPanelTabDefinition )
		{
			$this->RaiseEvent( "OnSearchBoundTabSelected", $tab );
		}
	}

	protected function InflateTabDefinitions()
	{
		$inflatedTabDefinitions = [];

		foreach( $this->_tabs as $key => $value )
		{
			if ( $value instanceof TabDefinition )
			{
				$inflatedTabDefinitions[] = $value;
			}
			elseif ( is_string( $key ) )
			{
				if ( is_array( $value ) )
				{
					$inflatedTabDefinitions[] = new SearchPanelTabDefinition( $key, $value );
				}
				else
				{
					$inflatedTabDefinitions[] = new TabDefinition( $key, $value );
				}
			}
		}

		return $inflatedTabDefinitions;
	}

	protected function MarkSelectedTab( &$inflatedTabDefinitions )
	{
		$currentSearchValues = $this->RaiseEvent( "GetSearchControlValues" );

		$anySelected = false;

		if( $currentSearchValues !== null )
		{
			foreach( $inflatedTabDefinitions as $tab )
			{
				$same = true;

				foreach( $tab->data as $key => $value )
				{
					if( !isset( $currentSearchValues[ $key ] ) )
					{
						$same = false;
						break;
					}

					if( $currentSearchValues[ $key ] !== $value )
					{
						$same = false;
						break;
					}
				}

				foreach( $currentSearchValues as $key => $value )
				{
					if( !isset( $tab->data[ $key ] ) )
					{
						if( $value !== false && $value !== null && $value !== "" )
						{
							$same = false;
							break;
						}

						continue;
					}

					if( $tab->data[ $key ] !== $value )
					{
						$same = false;
						break;
					}
				}

				if( $same )
				{
					$anySelected = true;
					$tab->selected = true;
				}
				else
				{
					$tab->selected = false;
				}
			}
		}
		else
		{
			$currentSearchValues = [];
		}

		if( !$anySelected )
		{
			$inflatedTabDefinitions[ ] = $searchResults = new SearchResultsTabDefinition( "Search Results" );
			$searchResults->data = $currentSearchValues;
			$searchResults->selected = true;
		}
	}

	protected function bindEvents( Presenter $presenter )
	{
		if ( $presenter instanceof SearchPanel )
		{
			$this->attachEventHandler( "OnSearchBoundTabSelected", function( SearchPanelTabDefinition $tabDefinition ) use ( $presenter )
			{
				$presenter->setSearchControlValues( $tabDefinition->data );
			});

			$presenter->attachEventHandler( "Search", function()
			{
				$this->rePresent();
			});
		}
	}
}