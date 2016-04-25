<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds;

use Rhubarb\Leaf\Views\View;

class NormalView extends View
{
    public function printViewContent()
    {
        print "My New View";
    }
}