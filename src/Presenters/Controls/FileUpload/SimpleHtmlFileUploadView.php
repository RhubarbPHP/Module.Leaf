<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

require_once __DIR__."/../ControlView.class.php";

use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Presenters\PresenterDeploymentPackage;

class SimpleHtmlFileUploadView extends ControlView
{
	public $filters = [];

	public function __construct()
	{
		$this->_requiresContainer = false;
		$this->_requiresStateInputs = false;
	}

	protected function printViewContent()
	{
		$this->PrintUploadInput();
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/SimpleHtmlFileUploadViewBridge.js";

		return $package;
	}

	protected function getClientSideViewBridgeName()
	{
		return "SimpleHtmlFileUploadViewBridge";
	}

	/**
	 * Prints the upload input itself.
	 *
	 * Extending view classes should call this at some point in their PrintViewContent() method.
	 */
	protected function PrintUploadInput()
	{
		$accepts = "";

		if ( sizeof( $this->filters ) > 0 )
		{
			$accepts = " accept=\"".implode(",", $this->filters )."\"";
		}

		?>
		<input type="file" name="<?=$this->GetIndexedPresenterPath();?>" id="<?=$this->GetIndexedPresenterPath();?>" presenter-name="<?=$this->presenterName?>"<?= $accepts.$this->GetHtmlAttributeTags().$this->GetClassTag() ?>/>
		<?php
	}
}