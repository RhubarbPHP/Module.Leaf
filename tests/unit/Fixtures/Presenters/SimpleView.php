<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Views\View;

class SimpleView extends View
{
    public function __construct()
    {
        parent::__construct();

        $this->requiresContainer = false;
        $this->requiresStateInputs = false;
    }

    public function printViewContent()
    {
        print $this->model->text;
        print $this->presenters["ForenameA"];
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
