<?php

namespace Rhubarb\Leaf\Presenters\Controls\DateTime;

require_once __DIR__."/../Text/TextBox/TextBoxView.class.php";

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBoxView;

class DateView extends TextBoxView
{
	protected function getClientSideViewBridgeName()
	{
		return "DatePicker";
	}

	protected function getAdditionalResourceUrls()
	{
		return [
			ResourceLoader::GetJqueryUrl( "1.9.1" ),
			ResourceLoader::GetJqueryUIUrl( "1.10.1" ),
			"/client/jquery/css/jquery-ui.css",
			"/client/jquery/css/jquery.ui.theme.css" ];
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/../../../ClientSide/Resources/jquery-presenter.js";
		$package->resourcesToDeploy[] = __DIR__."/date-picker.js";

		return $package;
	}

}