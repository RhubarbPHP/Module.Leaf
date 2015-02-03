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
        $this->view->attachEventHandler(
            "FileUploadedXhr",
            function () {
                return $this->parseRequestForCommand();
            }
        );
    }
} 