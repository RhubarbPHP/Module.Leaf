<?php

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Tests\Fixtures\TestLeafModel;
use Rhubarb\Leaf\Views\View;

class LeafTest extends LeafTestCase
{
    public function testCanRenderView()
    {
        $response = $this->renderLeafAndGetContent();

        $this->assertContains(TestLeafView::FIXED_CONTENT, $response);
    }

    public function testViewCanBeSwapped()
    {
        $this->application->container()->registerClass(TestLeafView::class, TestLeafAlternativeView::class);
        // Recreate the leaf now that the view registration has changed.
        $this->leaf = $this->createLeaf();

        $response = $this->renderLeafAndGetContent();

        $this->assertContains(TestLeafAlternativeView::FIXED_CONTENT, $response);
    }

    public function testModelSharing()
    {
        $this->leaf = new TestLeafModelSharing();
        $this->leaf->setMessage("ABC123");

        $response = $this->renderLeafAndGetContent();

        $this->assertContains("ABC123", $response);
    }

    public function testEvents()
    {
        $this->leaf = new TestLeafModelSharing();
        $this->leaf->setMessage("ABC123");

        $model = TestLeafModelSharingModel::getModel();
        $model->changeMessageEvent->raise("A different message");

        $response = $this->renderLeafAndGetContent();

        $this->assertContains("A different message", $response);
    }

    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new TestLeaf();
    }
}

//////////////////// Fixtures ///////////////////////////


class TestLeaf extends Leaf
{
    protected function getViewClass()
    {
        return TestLeafView::class;
    }

    protected function createModel()
    {
        return new LeafModel();
    }
}

class TestLeafView extends View
{
    const FIXED_CONTENT = "Sample content A";

    protected function printViewContent()
    {
        print static::FIXED_CONTENT;
    }
}

class TestLeafAlternativeView extends TestLeafView
{
    const FIXED_CONTENT = "Sample content B";
}

class TestLeafModelSharing extends Leaf
{
    protected function getViewClass()
    {
        return TestLeafModelSharingView::class;
    }

    protected function createModel()
    {
        $model = new TestLeafModelSharingModel();
        $model->changeMessageEvent->attachHandler(function($message){
           $this->model->message =  $message;
        });

        return $model;
    }

    public function setMessage($message)
    {
        $this->model->message = $message;
    }
}

class TestLeafModelSharingView extends View
{
    /**
     * @var TestLeafModelSharingModel
     */
    protected $model;

    protected function printViewContent()
    {
        print $this->model->message;
    }
}

class TestLeafModelSharingModel extends TestLeafModel
{
    public $message = "";
    public $changeMessageEvent;

    public function __construct()
    {
        parent::__construct();

        $this->changeMessageEvent = new Event();
    }
}