<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Leaf\Presenters\Presenter;

class Simple extends Presenter
{
    public function __construct()
    {
        parent::__construct("Simple");

        $this->attachEventHandler("FirstEvent", function () {
            $this->lastEventProcessed = "FirstEvent";
        });

        $this->attachEventHandler("SecondEvent", function () {
            $this->lastEventProcessed = "SecondEvent";
        });
    }

    public function removeEventHandlers()
    {
        $this->clearEventHandlers();
    }

    protected function getPublicModelPropertyList()
    {
        $properties = parent::getPublicModelPropertyList();
        $properties[] = "ModelSetting";

        return $properties;
    }

    public $supportsLatePresenterRegistration = false;

    protected function supportsLateSubPresenterRegistration()
    {
        return $this->supportsLatePresenterRegistration;
    }

    protected function parseRequestForCommand()
    {
        parent::parseRequestForCommand();

        // Fire two events for our unit test.
        $this->raiseDelayedEvent("FirstEvent");
        $this->raiseEvent("SecondEvent");
    }

    /**
     * Examined by unit test.
     *
     * @var string
     */
    public $lastEventProcessed = "";

    protected function createView()
    {
        return new SimpleView();
    }

    public function getSubPresenters()
    {
        return $this->subPresenters;
    }

    protected function configureView()
    {
        parent::configureView();

        $this->view->attachEventHandler("Save", function () {
            $this->save();
        });

        $this->view->setText("Don't change this content - it should match the unit test.");
    }

    protected function save()
    {

    }

    protected function commandUpdateText($text = "The text has changed!")
    {
        $this->view->setText($text);
    }
}
