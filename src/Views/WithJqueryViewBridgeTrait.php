<?php

namespace Rhubarb\Leaf\Views;

trait WithJqueryViewBridgeTrait
{
	protected function GetClientSideViewBridgeName()
	{
		$className = get_class();
		$className = substr( $className, strrpos( $className, "\\" ) + 1 );
		return $className."Bridge";
	}

	public function GetDeploymentPackage()
	{
		$package = parent::GetDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/../ClientSide/Resources/jquery-presenter.js";
		$package->resourcesToDeploy[] = $this->GetDeploymentPackageDirectory()."/".$this->GetClientSideViewBridgeName().".js";

		return $package;
	}

	/**
	 * Implement this and return __DIR__ when your ViewBridge.js is in the same folder as your class
	 *
	 * @returns string Path to your ViewBridge.js file
	 */
	abstract public function GetDeploymentPackageDirectory();
}