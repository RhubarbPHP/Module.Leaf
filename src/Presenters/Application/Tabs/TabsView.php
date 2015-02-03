<?php

namespace Rhubarb\Leaf\Presenters\Application\Tabs;

use Rhubarb\Leaf\Views\JQueryView;

class TabsView extends JQueryView
{
	/**
	 * @var TabDefinition[]
	 */
	protected $_tabs;

	public function SetTabDefinitions( $tabs )
	{
		$this->_tabs = $tabs;
	}

	protected function getClientSideViewBridgeName()
	{
		return "Tabs";
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/TabsPresenter.js";
		//$package->resourcesToDeploy[] = __DIR__."/TabsPresenter.css";

		return $package;
	}

	protected function PrintTab( $tab )
	{
		$selected = ( $tab->selected ) ? " class=\"-is-selected\"" : "";
		print "<li{$selected}><a href='#'>".$tab->label."</a></li>";
	}

	public function printViewContent()
	{
		print "<ul class='tabs'>";

		foreach( $this->_tabs as $tab )
		{
			$this->PrintTab( $tab );
		}

		print "</ul>";
	}
}