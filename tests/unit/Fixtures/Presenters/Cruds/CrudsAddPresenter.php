<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds;

use Rhubarb\Leaf\Views\HtmlView;
use Rhubarb\Patterns\Mvp\Crud\ModelForm\ModelFormPresenter;

class CrudsAddPresenter extends ModelFormPresenter
{
    protected function createView()
    {
        return new HtmlView();
    }
}
