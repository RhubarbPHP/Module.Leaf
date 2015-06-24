<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Views\View;

class SimpleView extends View implements ISimpleView
{
    private $text;

    public function PrintViewContent()
    {
        print $this->text;
    }

    public function SetText($text)
    {
        $this->text = $text;
    }

    public function CreatePresenters()
    {
        parent::CreatePresenters();

        $this->AddPresenters(
            [
                "ForenameA" => new TextBox("Forename"),
                "ForenameB" => new TextBox("Forename")
            ]
        );
    }

}
