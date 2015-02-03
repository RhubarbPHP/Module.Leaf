<?php

namespace Rhubarb\Leaf\Presenters\Controls;

/**
 * Provides a method for Presenters to compile the view bridge spawn settings of themselves and any
 * sub-presenters, recursive to any level of nested sub-presenters that share this trait.
 *
 * @author nsmyth
 * @copyright GCD Technologies 2014
 */
trait SpawnableByViewBridgeWithSubPresenters
{
	public function CompileSpawnableSettings()
	{
		$spawnSettings = [];

		foreach( $this->subPresenters as $presenter )
		{
			$presenterName = $presenter->GetName();
			$spawnSettings[ $presenterName ] = $presenter->GetSpawnStructure();

			if( method_exists( $presenter, "CompileSpawnableSettings" ) )
			{
				$spawnSettings[ $presenterName ][ "SubPresenters" ] = $presenter->CompileSpawnableSettings();
			}
		}

		$this->model->PresenterSpawnSettings = $spawnSettings;
		return $spawnSettings;
	}
}
