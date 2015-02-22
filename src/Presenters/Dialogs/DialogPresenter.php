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

namespace Rhubarb\Leaf\Presenters\Dialogs;

require_once __DIR__ . "/../HtmlPresenter.php";
require_once __DIR__ . "/../ModelProvider.php";

use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;

abstract class DialogPresenter extends HtmlPresenter
{
    use ModelProvider;

    public function __construct($name = "")
    {
        if ($name == "") {
            $name = str_replace("Presenter", "", basename(str_replace("\\", "/", get_class($this))));
        }

        parent::__construct($name);
    }

    public function setPreferredWidth($width)
    {
        $this->model->PreferredWidth = $width;
    }

    public function setPreferredHeight($height)
    {
        $this->model->PreferredHeight = $height;
    }

    protected function getPublicModelPropertyList()
    {
        $list = parent::getPublicModelPropertyList();
        $list[] = "PreferredWidth";
        $list[] = "PreferredHeight";

        return $list;
    }

    protected function onResponseGenerated($html)
    {
        $html = preg_replace("|^<div id=|", "<div style=\"display: none\" class=\"dialog-container\" id=", $html);

        return $html;
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->attachEventHandler(
            "GetDialogData",
            function ($uniqueIdentifier) {
                return $this->getDialogData($uniqueIdentifier);
            }
        );
    }

    /**
     * Implement this function to support fetching existing data for the dialog.
     *
     * It is essential not to send back data which should be kept private. Remember the response to this
     * function will be passed back to the client "in the clear" (SSL not withstanding)
     *
     * @param $uniqueIdentifier
     * @return array
     */
    protected function getDialogData($uniqueIdentifier)
    {
        return [];
    }
}