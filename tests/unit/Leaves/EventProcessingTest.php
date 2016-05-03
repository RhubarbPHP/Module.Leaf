<?php

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Views\View;

class EventProcessingTest extends LeafTestCase
{
    public function testAfterEventsProcessing()
    {
        $model = EventProcessingSubLeafModel::$instance;
        $model->delayedEvent->raise();
        $model->instantEvent->raise();

        $this->renderLeafAndGetContent();

        $this->assertEquals("delayed", $model->output);
    }

    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new EventProcessingLeaf();
    }
}

class EventProcessingLeaf extends Leaf
{

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return EventProcessingView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new LeafModel();
    }
}

class EventProcessingView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            new EventProcessingSubLeaf()
        );
    }

}


class EventProcessingSubLeaf extends Leaf
{
    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return View::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        $model = new EventProcessingSubLeafModel();
        $model->instantEvent->attachHandler(function(){
            $this->model->output = "instant";
        });
        $model->delayedEvent->attachHandler(function(){
            $this->runBeforeRender(function(){
                $this->model->output = "delayed";
            });
        });
        return $model;
    }
}

class EventProcessingSubLeafModel extends LeafModel
{
    public static $instance;

    /**
     * @var Event
     */
    public $delayedEvent;

    /**
     * @var Event
     */
    public $instantEvent;

    public $output = "";

    public function __construct()
    {
        self::$instance = $this;
        $this->delayedEvent = new Event();
        $this->instantEvent = new Event();
    }


}