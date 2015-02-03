<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\SearchControl;

require_once __DIR__."/../SelectionControlView.class.php";

use Rhubarb\Leaf\Presenters\Controls\Buttons\Button;
use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlView;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class SearchControlView extends SelectionControlView
{
	public function printViewContent()
	{
		print '<input type="hidden" name="'.$this->GetIndexedPresenterPath().'" />';
	}

	protected function getClientSideViewBridgeName()
	{
		return "SearchControl";
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/search-control.js";
		$package->resourcesToDeploy[] = __DIR__."/SearchControl.css";
		$package->resourcesToDeploy[] = __DIR__."/Resources/ajax-loader.gif";

		return $package;
	}
}