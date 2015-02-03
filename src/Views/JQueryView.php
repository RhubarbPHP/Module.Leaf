<?php

namespace Rhubarb\Leaf\Views;

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Views\HtmlView;

class JQueryView extends HtmlView
{
	protected function getAdditionalResourceUrls()
	{
		return [ ResourceLoader::GetJqueryUrl( "1.9.1" ) ];
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/../ClientSide/Resources/jquery-presenter.js";

		return $package;
	}
}