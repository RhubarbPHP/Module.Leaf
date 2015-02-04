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

namespace Rhubarb\Leaf\Presenters\Controls\Buttons;

require_once __DIR__ . "/../JQueryControlView.php";

use Rhubarb\Leaf\Presenters\Controls\JQueryControlView;

class ButtonView extends JQueryControlView
{
    private $text;

    public $useXmlRpc = false;
    public $validator = null;
    public $validatorHostPresenterPath = "";

    private $confirmMessage = "";
    private $inputType = "submit";

    public function __construct()
    {
        $this->requiresContainer = false;
        $this->requiresStateInputs = false;
    }

    public function printViewContent()
    {
        $xmlAttribute = ($this->useXmlRpc) ? " xmlrpc=\"yes\"" : "";
        $validationAttribute = ($this->validator != null) ? " validation=\"" . htmlentities(
                json_encode($this->validator->GetJsonStructure())
            ) . "\"" : "";
        $validatorAttribute = ($this->validatorHostPresenterPath) ? " validator=\"" . htmlentities(
                $this->validatorHostPresenterPath
            ) . "\"" : "";
        $confirmAttribute = ($this->confirmMessage != "") ? " confirm=\"" . htmlentities(
                $this->confirmMessage
            ) . "\"" : "";

        ?>
        <input type="<?= $this->inputType ?>" name="<?= htmlentities($this->getIndexedPresenterPath()); ?>"
               presenter-name="<?= htmlentities($this->presenterName); ?>"
               id="<?= htmlentities($this->getIndexedPresenterPath()); ?>"
               value="<?= htmlentities($this->text); ?>"<?= $this->getClassTag() . $this->getHtmlAttributeTags(
        ) . $xmlAttribute . $validationAttribute . $validatorAttribute . $confirmAttribute ?>/>
    <?php
    }

    public function setButtonText($text)
    {
        $this->text = $text;

        return $this;
    }

    public function setButtonType($type)
    {
        $this->inputType = $type;

        return $this;
    }

    public function setConfirmMessage($confirmMessage)
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    protected function getClientSideViewBridgeName()
    {
        return "Button";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = "vendor/rhubarbphp/rhubarb/resources/validation.js";
        $package->resourcesToDeploy[] = __DIR__ . "/button.js";

        return $package;
    }
}
