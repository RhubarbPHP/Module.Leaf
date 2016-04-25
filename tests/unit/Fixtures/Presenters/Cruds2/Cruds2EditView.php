<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds2;

use Rhubarb\Leaf\Views\View;

class Cruds2EditView extends View
{
    protected function printViewContent()
    {
        $user = $this->RaiseEvent("GetRestModel");

        print $user->Forename;
    }
}