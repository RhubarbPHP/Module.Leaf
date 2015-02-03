<?php

namespace Rhubarb\Leaf\Presenters\Controls;

require_once __DIR__."/ControlView.class.php";

use Rhubarb\Crown\Html\ResourceLoader;

class JQueryControlView extends ControlView
{
	protected function getAdditionalResourceUrls()
	{
		return [ ResourceLoader::GetJqueryUrl( "1.9.1" ) ];
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/../../ClientSide/Resources/jquery-presenter.js";

		return $package;
	}
}