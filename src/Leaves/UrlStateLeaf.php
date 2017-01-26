<?php

namespace Rhubarb\Leaf\Leaves;

use Rhubarb\Crown\Request\WebRequest;

abstract class UrlStateLeaf extends Leaf
{
    /** @var UrlStateLeafModel */
    protected $model;

    protected function createModel()
    {
        return new UrlStateLeafModel();
    }

    public function getUrlStateName()
    {
        return $this->model->urlStateName;
    }

    public function setUrlStateName($name)
    {
        $this->model->urlStateName = $name;
    }

    protected function parseRequest(WebRequest $request)
    {
        parent::parseRequest($request);

        $this->parseUrlState($request);
    }

    protected function parseUrlState(WebRequest $request)
    {
        // Override this method to parse the state from the URL for this leaf.
    }
}
