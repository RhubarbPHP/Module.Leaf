<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

require_once __DIR__ . '/SimpleHtmlFileUploadView.php';

use Rhubarb\Crown\Imaging\Image;
use Rhubarb\Crown\Imaging\ImageProcessResize;

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