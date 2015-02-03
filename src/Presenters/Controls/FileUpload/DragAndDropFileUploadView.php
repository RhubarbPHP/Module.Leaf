<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

class DragAndDropFileUploadView extends MultipleHtmlFileUploadView
{
	protected function getClientSideViewBridgeName()
	{
		return "DragAndDropFileUploadViewBridge";
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/DragAndDropFileUploadViewBridge.js";

		return $package;
	}
}