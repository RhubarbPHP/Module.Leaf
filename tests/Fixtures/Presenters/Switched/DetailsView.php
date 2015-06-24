<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched;

use Rhubarb\Leaf\Views\View;

class DetailsView extends View
{
    public static $forename;

    public function createPresenters()
    {
        self::$forename = new UnitTestTextBox("Forename");

        $this->addPresenters(
            self::$forename
        );

        parent::createPresenters();
    }

    public function printViewContent()
    {
        print $this->presenters["Forename"];
    }
}
