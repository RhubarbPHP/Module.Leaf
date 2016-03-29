<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Views\View;

class SimpleView extends View implements ISimpleView
{
    private $text;

    public function printViewContent()
    {
        print $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function createPresenters()
    {
        parent::createPresenters();

        $this->addPresenters(
            [
                "ForenameA" => new TextBox("Forename"),
                "ForenameB" => new TextBox("Forename")
            ]
        );
    }

}
