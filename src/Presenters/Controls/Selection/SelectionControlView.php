<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection;

require_once __DIR__."/../JQueryControlView.class.php";

use Rhubarb\Leaf\Presenters\Controls\JQueryControlView;

class SelectionControlView extends JQueryControlView
{
	protected $_availableItems = [];

	public function SetAvailableItems( $items = [] )
	{
		$this->_availableItems = $items;
	}

	protected $_selectedItems = [];

	public function SetSelectedItems( $values = [] )
	{
		$this->_selectedItems = $values;
	}

	protected $_supportsMultiple = false;

	public function SetSupportsMultiple( $value )
	{
		$this->_supportsMultiple = $value;
	}

	public function GetSpawnSettings()
	{
		$settings = parent::GetSpawnSettings();
		$settings[ "AvailableItems" ] = $this->_availableItems;

		return $settings;
	}

	protected function getClientSideViewBridgeName()
	{
		return "SelectionControlViewBridge";
	}

	protected function IsValueSelected( $value )
	{
		foreach( $this->_selectedItems as $item )
		{
			if ( $item->value == $value )
			{
				return true;
			}
		}

		return false;
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/SelectionControlViewBridge.js";

		return $package;
	}
}