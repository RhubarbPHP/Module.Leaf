<?php

namespace Rhubarb\Leaf\Presenters\Application\Tabs;

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\Presenter;

class TabsPresenter extends HtmlPresenter
{
	protected $_tabs = [];

	protected function createView()
	{
		return new TabsView();
	}

	public function SetTabDefinitions( $tabs = [] )
	{
		$this->_tabs = $tabs;
	}

	public function GetTabDefinitions()
	{
		return $this->_tabs;
	}

	public function GetTabByIndex( $tabIndex )
	{
		$tabs = $this->GetInflatedTabDefinitions();

		return $tabs[ $tabIndex ];
	}

	protected final function GetInflatedTabDefinitions()
	{
		$tabs = $this->InflateTabDefinitions();
		$this->MarkSelectedTab( $tabs );

		return $tabs;
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
				$inflatedTabDefinitions[] = new TabDefinition( $key, $value );
			}
		}

		return $inflatedTabDefinitions;
	}

	protected function configureView()
	{
		parent::configureView();

		$this->view->attachEventHandler( "TabSelected", function( $tabIndex )
		{
			$this->SelectTabByIndex( $tabIndex );
		});
	}

	protected function MarkSelectedTab( &$inflatedTabDefinitions )
	{
		if ( $this->SelectedTab !== null )
		{
			$inflatedTabDefinitions[ $this->SelectedTab ]->selected = true;
		}
	}

	protected function applyModelToView()
	{
		$tabs = $this->GetInflatedTabDefinitions();

		$this->view->SetTabDefinitions( $tabs );

		parent::applyModelToView();
	}

	/**
	 * Set's the selected tab to the one indexed by $index
	 *
	 * Triggers the SelectedTabChanged event.
	 *
	 * @param $tabIndex
	 */
	public function SelectTabByIndex( $tabIndex )
	{
		$this->model->SelectedTab = $tabIndex;

		$this->RaiseEvent( "SelectedTabChanged", $tabIndex );

		$this->OnSelectedTabChanged( $tabIndex );
	}

	/**
	 * Override to perform actions when the selected tab changes.
	 */
	protected function OnSelectedTabChanged( $tabIndex )
	{

	}
}