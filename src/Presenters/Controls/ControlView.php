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

namespace Rhubarb\Leaf\Presenters\Controls;

require_once __DIR__ . "/../../Views/SpawnableByViewBridgeView.php";

use Rhubarb\Leaf\Views\SpawnableByViewBridgeView;

class ControlView extends SpawnableByViewBridgeView
{
    public $cssClassNames = [];
    public $htmlAttributes = [];

    protected function getClassTag()
    {
        if (is_array($this->cssClassNames) && sizeof($this->cssClassNames)) {
            return " class=\"" . implode(" ", $this->cssClassNames) . "\"";
        }

        return "";
    }

    protected function getHtmlAttributeTags()
    {
        if (is_array($this->htmlAttributes) && sizeof($this->htmlAttributes)) {
            $attributes = [];

            foreach ($this->htmlAttributes as $key => $value) {
                $attributes[] = $key . "=\"" . htmlentities($value) . "\"";
            }

            return " " . implode(" ", $attributes);
        }

        return "";
    }

    protected function getNameValueClassAndAttributeString()
    {
        $classes = $this->getClassTag();
        $otherAttributes = $this->getHtmlAttributeTags();

        $string = 'leaf-name="'.$this->getClientSideViewBridgeName().'" name="'.$this->getClientSideViewBridgeFilePath().'" id="'.$this->getClientSideViewBridgeFilePath().'" '.$classes.$otherAttributes;

        return $string;
    }
}
