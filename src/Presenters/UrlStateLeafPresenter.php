<?php

namespace Rhubarb\Leaf\Presenters;

use Rhubarb\Crown\Context;
use Rhubarb\Crown\Request\Request;
use Rhubarb\Leaf\Presenters\HtmlPresenter;

class UrlStateLeafPresenter extends HtmlPresenter
{
    /**
     * @var string The name of the GET param which will provide state for this leaf in the URL
     */
    public $urlStateName;

    public function __construct($name = "")
    {
        parent::__construct($name);

        $this->model->urlStateName = $this->urlStateName;
    }


    protected function getPublicModelPropertyList()
    {
        $properties = parent::getPublicModelPropertyList();
        $properties[] = 'urlStateName';
        return $properties;
    }

    public function getUrlStateName()
    {
        return $this->model->urlStateName;
    }

    public function setUrlStateName($name)
    {
        $this->model->urlStateName = $name;
    }

    protected function parseRequestForCommand()
    {
        parent::parseRequestForCommand();

        $request = Context::currentRequest();
        $this->parseUrlState($request);
    }


    protected function parseUrlState(Request $request)
    {
        // Override this method to parse the state from the URL for this leaf.
    }
}
