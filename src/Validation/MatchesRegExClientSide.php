<?php

namespace Rhubarb\Leaf\Validation;

use Rhubarb\Stem\Models\Validation\MatchesRegEx;

class MatchesRegExClientSide extends MatchesRegEx
{
    use ClientSideValidation;

    protected function getValidationSettings()
    {
        return [
            "regEx" => $this->regEx
        ];
    }

    public static function cloneFromModelValidation(MatchesRegEx $validation)
    {
        return new MatchesRegExClientSide($validation->name, $validation->regEx);
    }
}