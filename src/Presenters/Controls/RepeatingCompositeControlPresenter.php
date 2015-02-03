<?php

namespace Rhubarb\Leaf\Presenters\Controls;

class RepeatingCompositeControlPresenter extends CompositeControlPresenter
{
	private function CompileSpawnableSettings()
	{
		$spawnSettings = [];

		foreach( $this->subPresenters as $presenter )
		{
			$spawnSettings[ $presenter->GetName() ] = $presenter->GetSpawnStructure();
		}

		$this->model->PresenterSpawnSettings = $spawnSettings;
	}

	protected function OnModelAppliedToView()
	{
		$this->CompileSpawnableSettings();

		parent::OnModelAppliedToView();
	}

	protected function getPublicModelPropertyList()
	{
		$properties = parent::getPublicModelPropertyList();
		$properties[] = "PresenterSpawnSettings";

		return $properties;
	}
}