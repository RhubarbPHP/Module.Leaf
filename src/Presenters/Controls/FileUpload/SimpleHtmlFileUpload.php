<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

require_once __DIR__."/../ControlPresenter.class.php";

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class SimpleHtmlFileUpload extends ControlPresenter
{
	/**
	 * An array of accepted file types.
	 *
	 * The values should be either:
	 * 1. A file extension prefixed by . e.g. .pdf
	 * 2. One of the following categories of file: audio/* video/* image/*
	 * 3. A valid mime file type e.g. text/plain
	 *
	 * @var array
	 */
	public $filters = [];

	protected function createView()
	{
		return new SimpleHtmlFileUploadView();
	}

	protected function parseRequestForCommand()
	{
		$request = Context::CurrentRequest();
		$fileData = $request->Files( $this->getIndexedPresenterPath() );

        $response = null;
        
		if ( $fileData !== null )
		{
			if ( isset( $fileData[ "name" ] ) )
			{
				if ( is_array( $fileData[ "name" ] ) )
				{
					foreach( $fileData[ "name" ] as $index => $name )
					{
						if ( $fileData[ "error" ][ $index ] == UPLOAD_ERR_OK )
						{
							$realIndex = str_replace( "_", "", $index );
							$response = $this->RaiseEvent( "FileUploaded", $name, $fileData[ "tmp_name" ][ $index ], $realIndex );
						}
					}
				}
				else
				{
					if ( $fileData[ "error" ] == UPLOAD_ERR_OK )
					{
						$response = $this->RaiseEvent( "FileUploaded", $fileData[ "name" ], $fileData[ "tmp_name" ], $this->_viewIndex );
					}
				}
			}
		}

		parent::parseRequestForCommand();

        return $response;
	}

	protected function applyModelToView()
	{
		$this->view->filters = $this->filters;

		parent::applyModelToView();
	}
}