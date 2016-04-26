<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Presenter;

class Simple extends Presenter
{
    public function __construct()
    {
        parent::__construct("Simple");

        $this->model->firstEvent->attachHandler(function () {
            $this->lastEventProcessed = "FirstEvent";
        });

        $this->model->secondEvent->attachHandler(function () {
            $this->lastEventProcessed = "SecondEvent";
        });
    }

    /**
     * The overriding class should implement to return a model class that extends PresenterModel
     *
     * This is normally done with an anonymous class for convenience
     *
     * @return PresenterModel
     */
    protected function createModel()
    {
        return new SimpleModel();
    }

    protected function parseRequestForCommand()
    {
        parent::parseRequestForCommand();

        // Fire two events for our unit test.
        $this->runAfterEventsProcessed(function(){
            $this->model->firstEvent->raise();
        });

        $this->model->secondEvent->raise();
    }

    /**
     * Examined by unit test.
     *
     * @var string
     */
    public $lastEventProcessed = "";

    protected function createView()
    {
        $this->model->saveEvent->attachHandler(function () {
            $this->save();
        });

        $this->model->text = "Don't change this content - it should match the unit test.";

        return new SimpleView();
    }

    public function getSubPresenters()
    {
        return $this->subPresenters;
    }

    protected function save()
    {

    }

    public function updateText($text = "The text has changed!")
    {
        $this->model->text = $text;
    }
}
