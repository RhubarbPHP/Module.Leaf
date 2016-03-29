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

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__ . "/Presenter.php";

use Rhubarb\Leaf\Validation\ClientSideValidation;
use Rhubarb\Leaf\Validation\ValidatorClientSide;
use Rhubarb\Stem\Models\Validation\Validation;
use Rhubarb\Stem\Models\Validation\Validator;

class HtmlPresenter extends Presenter
{
    /**
     * Used to cache the default client side validator so it isn't created every time getDefaultClientSideValidator is called
     * @var Validator
     */
    protected $defaultClientSideValidator;

    /**
     * Returns by default the server side validator (which should be created using
     * the appropriate ClientSide variants.)
     *
     * Override to provide different validation on the client side. Ignore completely if you aren't using
     * the default validation behaviours.
     *
     * @return \Rhubarb\Stem\Models\Validation\Validator
     */
    protected function createDefaultClientSideValidator()
    {
        $validation = $this->getDefaultValidator();

        if (!$validation instanceof Validation) {
            return null;
        }

        if ($validation instanceof Validation &&
            !in_array(ClientSideValidation::class, ValidatorClientSide::nestedClassUses($validation))
        ) {
            // Convert the validation to a client side validation if required. If the validation doesn't have a
            // matching client side version, null will returned essentially disabling the client side validation.
            $validation = ClientSideValidation::fromModelValidation($validation);
        }

        return $validation;
    }

    protected function getDefaultClientSideValidator()
    {
        if (!$this->defaultClientSideValidator) {
            $this->defaultClientSideValidator = $this->createDefaultClientSideValidator();
        }
        return $this->defaultClientSideValidator;
    }

    public function getPlaceholderDefaultContentByName($validationName)
    {
        $defaultValidator = $this->getDefaultClientSideValidator();

        foreach ($defaultValidator->validations as $validation) {
            if ($validation->name == $validationName) {
                return "*";
            }
        }

        return "";
    }

    protected function onViewRegistered()
    {
        parent::onViewRegistered();

        $this->view->attachEventHandler(
            "GetDefaultClientSideValidator",
            function () {
                return $this->getDefaultClientSideValidator();
            }
        );
    }
}
