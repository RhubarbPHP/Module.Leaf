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

use Rhubarb\Crown\ClientSide\Validation\ValidatorClientSide;
use Rhubarb\Crown\Context;
use Rhubarb\Crown\Exceptions\ImplementationException;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class Button extends ControlPresenter
{
    private $temporaryButtonText = "";

    private $confirmMessage = "";

    private $buttonType = "submit";

    public $validator = null;

    public $validatorHostPresenterPath = "";

    public $useXhr = false;

    public function __construct($name = "", $buttonText = "", $onButtonPressed = null, $useXhr = false)
    {
        parent::__construct($name);

        $this->addCssClassName("btn");

        $this->temporaryButtonText = $buttonText;
        $this->useXhr = $useXhr;

        $this->attachClientSidePresenterBridge = true;

        if ($onButtonPressed != null) {
            if (!is_callable($onButtonPressed)) {
                throw new ImplementationException('onButtonPressed must be callable.');
            }

            $this->attachEventHandler("ButtonPressed", $onButtonPressed);
        }
    }

    protected function initialiseModel()
    {
        parent::initialiseModel();

        if ($this->model->ButtonText === null) {
            $this->setButtonText($this->temporaryButtonText);
        }
    }

    public function setButtonText($buttonText)
    {
        $this->model->ButtonText = $buttonText;

        return $this;
    }

    public function setValidator(ValidatorClientSide $validator)
    {
        $this->validator = $validator;

        return $this;
    }

    public function setConfirmMessage($confirmMessage)
    {
        $this->confirmMessage = $confirmMessage;

        return $this;
    }

    public function setButtonType($type, $submitFormOnClick = false)
    {
        $this->buttonType = $type;

        if ($submitFormOnClick) {
            $this->addCssClassName("submit-on-click");
        }

        return $this;
    }

    protected function parseRequestForCommand()
    {
        $request = $request = Context::currentRequest();
        $pushed = $request->Post($this->getIndexedPresenterPath());

        if ($pushed != null) {
            $this->raiseDelayedEvent("ButtonPressed", $this->viewIndex);
        }
    }

    /**
     * Merely triggers the ButtonPressed event.
     *
     * Primarily used for unit testing.
     */
    public function simulateButtonPress()
    {
        $this->raiseEvent("ButtonPressed");
    }

    protected function wrapOuter($html)
    {
        $name = $this->getName();

        if ($name != "") {
            $html = str_replace("<input ", "<input presenter-name=\"" . htmlentities($name) . "\"", $html);
        }

        return parent::wrapOuter($html);
    }

    protected function createView()
    {
        return new ButtonView();
    }

    protected function applyModelToView()
    {
        $this->view->setButtonText($this->model->ButtonText);
        $this->view->useXmlRpc = $this->useXhr;
        $this->view->validator = $this->validator;
        $this->view->validatorHostPresenterPath = $this->validatorHostPresenterPath;
        $this->view->setConfirmMessage($this->confirmMessage);
        $this->view->setButtonType($this->buttonType);

        parent::applyModelToView();
    }
}