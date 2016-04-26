<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\TextBox;

use Rhubarb\Leaf\Presenters\Controls\ControlModel;

class TextBoxModel extends ControlModel
{
    public $size = 40;
    public $maxLength;
    public $allowBrowserAutoComplete = true;
    public $defaultValue = "";
    public $placeholderText = "";
    public $inputHtmlType;
}