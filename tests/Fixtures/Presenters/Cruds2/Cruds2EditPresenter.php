<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds2;

use Rhubarb\Patterns\Mvp\Crud\ModelForm\ModelFormPresenter;

class Cruds2EditPresenter extends ModelFormPresenter
{
    protected function createView()
    {
        return new Cruds2EditView();
    }

}
