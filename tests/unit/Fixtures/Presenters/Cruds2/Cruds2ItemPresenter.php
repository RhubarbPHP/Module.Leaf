<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds2;

use Rhubarb\Leaf\Views\View;
use Rhubarb\Patterns\Mvp\Crud\ModelForm\ModelFormPresenter;

class Cruds2ItemPresenter extends ModelFormPresenter
{
    protected function createView()
    {
        return new View();
    }
}
