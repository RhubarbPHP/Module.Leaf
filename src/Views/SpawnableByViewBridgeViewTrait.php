<?php

namespace Rhubarb\Leaf\Views;

trait SpawnableByViewBridgeViewTrait
{
	/**
	 * Returns an array of settings which would be required to spawn a view bridge directly.
	 *
	 * @return array
	 */
	public function GetSpawnSettings()
	{
		$settings = $this->GetState();

		$settings[ "ViewBridgeClass" ] = $this->GetClientSideViewBridgeName();

		return $settings;
	}
}