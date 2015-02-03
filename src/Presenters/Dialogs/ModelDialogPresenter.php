<?php

namespace Rhubarb\Leaf\Presenters\Dialogs;

use Rhubarb\Stem\Schema\SolutionSchema;
use Rhubarb\Leaf\Presenters\CreatePresentersFromSchemaTrait;

abstract class ModelDialogPresenter extends DialogPresenter
{
	use CreatePresentersFromSchemaTrait;

	protected abstract function GetModelName();

	protected function GetRestModel()
	{
		$modelName = $this->GetModelName();

		return SolutionSchema::GetModel( $modelName );
	}
}