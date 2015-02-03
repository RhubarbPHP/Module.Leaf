<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

use Rhubarb\Crown\UnitTesting\CoreTestCase;

class SimpleHtmlFileUploadTest extends CoreTestCase
{
	public function testUploadEventIsTriggered()
	{
		$eventFilename = null;
		$eventPath = null;

		$upload = new SimpleHtmlFileUpload( "Image" );
		$upload->attachEventHandler( "FileUploaded", function( $filename, $path ) use ( &$eventFilename, &$eventPath )
		{
			$eventFilename = $filename;
			$eventPath = $path;
		} );

		$request = \Rhubarb\Crown\Context::CurrentRequest();
		$request->Files( "Image", [
			"name" => "goats-boats.jpg",
			"tmp_name" => "/temporary/path/abc123",
			"error" => UPLOAD_ERR_OK
		]);

		$upload->generateResponse( $request );

		$this->assertEquals( "goats-boats.jpg", $eventFilename );
		$this->assertEquals( "/temporary/path/abc123", $eventPath );
	}
}
