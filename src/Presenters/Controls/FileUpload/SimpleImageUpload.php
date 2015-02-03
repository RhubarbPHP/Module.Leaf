<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

use Rhubarb\Leaf\Presenters\PresenterModel;

/** 
 * 
 *
 * @package Rhubarb\Leaf\Presenters\Controls\FileUpload
 * @author      acuthbert
 * @copyright   2013 GCD Technologies Ltd.
 */
class SimpleImageUpload extends SimpleHtmlFileUpload
{
	public $previewImageWidth = 200;

	public $previewImageHeight = 150;

	public function __construct( $name = "", $previewImageWidth = 200, $previewImageHeight = 150 )
	{
		parent::__construct( $name );

		$this->filters[] = "image/*";

		$this->previewImageWidth = $previewImageWidth;
		$this->previewImageHeight = $previewImageHeight;
	}

	protected function createView()
	{
		return new SimpleImageUploadView();
	}

	protected function applyModelToView()
	{
		parent::applyModelToView();

		$currentPath = $this->RaiseEvent( "GetCurrentPath" );

		$this->view->currentImagePath = $currentPath;
		$this->view->previewImageWidth = $this->previewImageWidth;
		$this->view->previewImageHeight = $this->previewImageHeight;
	}
}