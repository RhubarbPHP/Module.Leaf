<?php

namespace Rhubarb\Leaf\Presenters\Controls;

use Rhubarb\Leaf\Views\JQueryView;

/** 
 * 
 *
 * @package Rhubarb\Leaf\Presenters\Controls
 * @author      acuthbert
 * @copyright   2013 GCD Technologies Ltd.
 */
class RepeatingCompositeControlView extends JQueryView
{
	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/RepeatingCompositeControlViewBridge.js";

		return $package;
	}

	protected function getClientSideViewBridgeName()
	{
		return "RepeatingCompositeControlViewBridge";
	}
}