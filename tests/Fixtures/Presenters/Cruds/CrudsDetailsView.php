<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters\Cruds;

use Rhubarb\Leaf\Views\HtmlView;

class CrudsDetailsView extends HtmlView
{
    public function printViewContent()
    {
        print "The details view";
    }
}