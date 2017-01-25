<?php

namespace Rhubarb\Leaf\Leaves;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Views\View;

class UrlStateView extends View
{
    public function getDeploymentPackage()
    {
        return new LeafDeploymentPackage(__DIR__ . '/UrlStateViewBridge.js');
    }

    protected function parseRequest(WebRequest $request)
    {
        parent::parseRequest($request);

        $this->parseUrlState($request);
    }

    protected function parseUrlState(WebRequest $request)
    {
        // Override this method to parse the state from the URL for sub-leaves of this view.
    }
}
