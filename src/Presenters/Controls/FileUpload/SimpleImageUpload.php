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

require_once __DIR__ . '/SimpleHtmlFileUpload.php';

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

		$currentPath = $this->raiseEvent( "GetCurrentPath" );

		$this->view->currentImagePath = $currentPath;
		$this->view->previewImageWidth = $this->previewImageWidth;
		$this->view->previewImageHeight = $this->previewImageHeight;
	}
}