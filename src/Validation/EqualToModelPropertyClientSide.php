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

use Rhubarb\Stem\Models\Validation\EqualToModelProperty;

class EqualToModelPropertyClientSide extends EqualToModelProperty
{
    use ClientSideValidation;

    protected function getValidationSettings()
    {
        return [
            "propertyName" => $this->equalTo
        ];
    }

    public static function cloneFromModelValidation(EqualToModelProperty $validation)
    {
        return new EqualToClientSide($validation->name, $validation->equalTo);
    }
}