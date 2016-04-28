<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Views\View;

class ControlView extends View
{
    public function setWebRequest(WebRequest $request)
    {
        parent::setWebRequest($request);
    }

    protected function printViewContent()
    {
        // Print your HTML here.
    }
}