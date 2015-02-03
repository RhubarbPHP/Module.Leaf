<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Views\SpawnableByViewBridgeView;
use Rhubarb\Leaf\Views\SpawnableByViewBridgeViewTrait;

/** 
 * Exposes support for spawning a representation of the presenter directly with the view bridge.
 *
 * @package Rhubarb\Leaf\Presenters
 * @author      acuthbert
 * @copyright   2013 GCD Technologies Ltd.
 */
class SpawnableByViewBridgePresenter extends Presenter
{
	public final function GetSpawnStructure()
	{
		$view = $this->view;

		if ( method_exists( $view, "GetSpawnSettings" ) )
		{
			$this->applyModelToView();

			$settings = $view->GetSpawnSettings();

			$deploymentPackage = $view->getDeploymentPackage();
			$deploymentPackage->Deploy();

			$urls = $deploymentPackage->GetDeployedUrls();

			ResourceLoader::LoadResource( $urls );

			return $settings;
		}

		return [];
	}
}