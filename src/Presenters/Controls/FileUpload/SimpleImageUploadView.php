<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

use Rhubarb\Crown\Imaging\Image;
use Rhubarb\Crown\Imaging\ImageProcessResize;

/** 
 * 
 *
 * @package Rhubarb\Leaf\Presenters\Controls\FileUpload
 * @author      acuthbert
 * @copyright   2013 GCD Technologies Ltd.
 */
class SimpleImageUploadView extends SimpleHtmlFileUploadView
{
	public $currentImagePath;

	public $previewImageWidth = 200;

	public $previewImageHeight = 150;

	public function printViewContent()
	{
		if ( $this->currentImagePath != "" )
		{
			try
			{
				$image = new Image( $this->currentImagePath );
				$image->AddProcess( new ImageProcessResize( $this->previewImageWidth, $this->previewImageHeight, true, true ) );

				$url = $image->DeployImage();

				print "<div><img src='$url' /></div>";
			}
			catch( \Exception $er )
			{

			}
		}

		parent::printViewContent();
	}
}