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

namespace Rhubarb\Leaf\Validation;

use Rhubarb\Stem\Models\Validation\Validation;

/**
 * Defines methods used to translate a server side validation into a client side json structure.
 *
 */
trait ClientSideValidation
{
    /**
     * Should return a key value pair array of validation settings used to create the client side validation object
     */
    protected function getValidationSettings()
    {
        return [];
    }


    /**
     * Returns a basic object containing just the relevant settings for the client side validation.
     *
     * @return \stdClass
     */
    public final function getJsonStructure()
    {
        $structure = new \stdClass();
        $structure->name = $this->name;
        $structure->type = basename(str_replace("\\", "/", str_replace("ClientSide", "", get_class($this))));
        $structure->failedMessage = $this->getFailedMessage();
        $structure->settings = $this->getValidationSettings();

        return $structure;
    }

    public static function cloneFromModelValidation(Validation $validation)
    {
        return null;
    }

    public final static function fromModelValidation(Validation $validation)
    {
        if (in_array("Rhubarb\Leaf\Validation\ClientSideValidation", class_uses($validation))) {
            return $validation;
        }

        $type = basename(str_replace("\\", "/", get_class($validation)));
        $clientSideClass = "Rhubarb\Leaf\Validation\\" . $type . "ClientSide";

        if (class_exists($clientSideClass)) {
            return call_user_func(array($clientSideClass, "cloneFromModelValidation"), $validation);
        }

        return null;
    }
}