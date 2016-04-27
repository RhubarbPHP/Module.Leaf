<?php

namespace Rhubarb\Leaf\Leaves;

use Codeception\Lib\Interfaces\Web;
use Rhubarb\Crown\DependencyInjection\Container;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Response\GeneratesResponseInterface;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\String\StringTools;
use Rhubarb\Leaf\Exceptions\InvalidLeafModelException;
use Rhubarb\Leaf\Views\View;

abstract class Leaf implements GeneratesResponseInterface
{
    /**
     * @var View
     */
    private $view;

    /**
     * @var LeafModel
     */
    protected $model;

    /**
     * A name for the leaf.
     *
     * If no name is given the class name will be used.
     *
     * @var string
     */
    private $name;

    /**
     * The WebRequest that the presenter is responding to.
     *
     * @var WebRequest
     */
    private $request;

    public function __construct($name = "")
    {
        $this->model = $this->createModel();

        $this->initialiseView();

        if ($this->model === null || !($this->model instanceof LeafModel)) {
            throw new InvalidLeafModelException("The call to createModel on ".get_class($this).
                " didn't return a LeafModel class");
        }

        if ($name == ""){
            $name = StringTools::getShortClassNameFromNamespace(static::class);
        }

        $this->name = $name;
        $this->model->leafName = $name;
    }

    protected function initialiseView()
    {
        $view = $this->createView();

        $this->view = $view;
    }

    /**
     * Get's the name of the leaf.
     *
     * @see $name
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected abstract function getViewClass();

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected abstract function createModel();

    private function createView()
    {
        $view = Container::instance($this->getViewClass(), $this->model);

        return $view;
    }

    public function setWebRequest(WebRequest $request)
    {
        $this->request = $request;

        if ($this->request) {
            $this->view->setWebRequest($this->request);
        }
    }

    private final function render()
    {
        $html = $this->view->renderContent();

        return $html;
    }

    /**
     * Renders the Leaf and returns an HtmlReponse to Rhubarb
     *
     * @param null $request
     * @return HtmlResponse
     */
    public function generateResponse($request = null)
    {
        $this->setWebRequest($request);

        $response = new HtmlResponse($this);
        $response->setContent($this->render());

        return $response;
    }

    function __toString()
    {
        return $this->render();
    }
}