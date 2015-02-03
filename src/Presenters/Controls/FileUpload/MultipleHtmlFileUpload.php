<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

class MultipleHtmlFileUpload extends SimpleHtmlFileUpload
{
    protected function getPublicModelPropertyList()
    {
        $properties = parent::getPublicModelPropertyList();
        $properties[] = "MaxFileSize";

        return $properties;
    }

    protected function createView()
    {
        return new MultipleHtmlFileUploadView();
    }

    protected function configureView()
    {
        parent::configureView();

        // Note that the view bridge raises the FileUploadedXhr event instead of simply
        // FileUploadedXhr. That is because our presenter's ParseRequestForCommand would also
        // being triggered if the two events where named the same and we would end up with a double
        // firing of the event.
        $this->view->attachEventHandler( "FileUploadedXhr", function()
        {
            return $this->parseRequestForCommand();
        });
    }
} 