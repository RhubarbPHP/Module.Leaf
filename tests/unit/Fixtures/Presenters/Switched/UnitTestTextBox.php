<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class UnitTestTextBox extends TextBox
{
    protected function applyModelToView()
    {
        parent::applyModelToView();

        self::$textBoxValue = $this->model->Text;
    }

    public static $textBoxValue = "";
}
