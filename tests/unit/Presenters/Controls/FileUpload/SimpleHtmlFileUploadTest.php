<?php

namespace Rhubarb\Leaf\Tests\Presenters\Controls\FileUpload;

use Rhubarb\Crown\Context;
use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Controls\FileUpload\SimpleHtmlFileUpload;

class SimpleHtmlFileUploadTest extends RhubarbTestCase
{
    public function testUploadEventIsTriggered()
    {
        $eventFilename = null;
        $eventPath = null;

        $upload = new SimpleHtmlFileUpload("Image");
        $upload->attachEventHandler("FileUploaded", function ($filename, $path) use (&$eventFilename, &$eventPath) {
            $eventFilename = $filename;
            $eventPath = $path;
        });

        $request = Context::currentRequest();
        $request->files("Image", [
            "name" => "goats-boats.jpg",
            "tmp_name" => "/temporary/path/abc123",
            "error" => UPLOAD_ERR_OK
        ]);

        $upload->generateResponse($request);

        $this->assertEquals("goats-boats.jpg", $eventFilename);
        $this->assertEquals("/temporary/path/abc123", $eventPath);
    }
}
