<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Crown\Context;
use Rhubarb\Crown\Request\Request;
use Rhubarb\Leaf\Views\JQueryView;

class UrlStateView extends JQueryView
{
    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/UrlStateViewBridge.js";

        return $package;
    }

    protected function parseRequestForCommand()
    {
        parent::parseRequestForCommand();

        $request = Context::currentRequest();
        $this->parseUrlState($request);
    }

    protected function parseUrlState(Request $request)
    {
        // Override this method to parse the state from the URL for sub-leaves of this view.
    }


}
