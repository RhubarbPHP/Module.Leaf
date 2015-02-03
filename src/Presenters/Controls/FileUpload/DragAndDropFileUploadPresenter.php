<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

use Rhubarb\Crown\Context;

class DragAndDropFileUploadPresenter extends MultipleHtmlFileUpload
{
    protected function createView()
    {
        return new DragAndDropFileUploadView();
    }
}