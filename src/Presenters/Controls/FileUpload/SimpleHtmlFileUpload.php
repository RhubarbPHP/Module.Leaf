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

require_once __DIR__ . "/../ControlPresenter.php";

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
        $request = Context::currentRequest();
        $fileData = $request->Files($this->getIndexedPresenterPath());

        $response = null;

        if ($fileData !== null) {
            if (isset($fileData["name"])) {
                if (is_array($fileData["name"])) {
                    foreach ($fileData["name"] as $index => $name) {
                        if ($fileData["error"][$index] == UPLOAD_ERR_OK) {
                            $realIndex = str_replace("_", "", $index);
                            $response = $this->raiseEvent(
                                "FileUploaded",
                                $name,
                                $fileData["tmp_name"][$index],
                                $realIndex
                            );
                        }
                    }
                } else {
                    if ($fileData["error"] == UPLOAD_ERR_OK) {
                        $response = $this->raiseEvent(
                            "FileUploaded",
                            $fileData["name"],
                            $fileData["tmp_name"],
                            $this->viewIndex
                        );
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