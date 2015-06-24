<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds;

use Rhubarb\Leaf\Presenters\HtmlPresenter;

class NormalPresenter extends HtmlPresenter
{
    public function createView()
    {
        return new NormalView();
    }
}