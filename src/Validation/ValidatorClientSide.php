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

use Rhubarb\Stem\Models\Validation\Validator;

class ValidatorClientSide extends Validator
{
    use ClientSideValidation;

    /**
     * class_uses only looks at the top level class. This shim allows all traits all the way down to be evaluated.
     */
    public static function nestedClassUses($class, $autoLoad = true)
    {
        $traits = [];

        do {
            $traits = array_merge(class_uses($class, $autoLoad), $traits);
        } while ($class = get_parent_class($class));

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoLoad), $traits);
        }

        return array_unique($traits);
    }

    protected function getValidationSettings()
    {
        $validationStructures = [];

        foreach ($this->validations as $validation) {
            if (!in_array("Rhubarb\Leaf\Validation\ClientSideValidation", self::nestedClassUses($validation))) {
                $validation = ClientSideValidation::fromModelValidation($validation);
            }

            $validationStructures[] = $validation->getJsonStructure();
        }

        return [
            "validateAll" => ($this->mode == self::VALIDATE_ALL),
            "validations" => $validationStructures
        ];
    }

    public static function cloneFromModelValidation(Validator $validation)
    {
        $validator = new ValidatorClientSide($validation->name, $validation->mode);
        $validator->validations = [];

        foreach ($validation->validations as $subValidation) {
            $clientSideSubValidation = ClientSideValidation::fromModelValidation($subValidation);

            if ($clientSideSubValidation != null) {
                $validator->validations[] = $clientSideSubValidation;
            }
        }

        return $validator;
    }
}