<?php

namespace Rhubarb\Leaf\Presenters\Dialogs;

require_once __DIR__."/../../Views/JQueryView.class.php";

use Rhubarb\Leaf\Views\JQueryView;

abstract class DialogView extends JQueryView
{
	protected function GetTitle()
	{
		return "Unnamed Dialog";
	}

	protected function PrintTitle()
	{
		?>
		<div class="dialog__title">
			<?=$this->GetTitle();?>
		</div>
		<?php
	}

	protected abstract function PrintDialogContent();

	public function printViewContent()
	{
		?>
		<div class="dialog">
		<?php

		$this->PrintTitle();

		?>
		<div class="dialog__content">
		<?php

		$this->PrintDialogContent();

		?>
		</div>
		</div>
		<?php
	}

	protected function getClientSideViewBridgeName()
	{
		return "DialogViewBridge";
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/DialogViewBridge.js";
		$package->resourcesToDeploy[] = __DIR__."/DialogViewBridge.css";

		return $package;
	}
}