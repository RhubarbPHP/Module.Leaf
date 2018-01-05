<?php

namespace Rhubarb\Leaf\Tests\Leaves\Controls;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Leaves\Controls\CompositeControl;
use Rhubarb\Leaf\Leaves\Controls\Control;
use Rhubarb\Leaf\Leaves\Controls\ControlView;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Views\View;

class CompositeControlTest extends LeafTestCase
{
    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new Host();
    }

    public function testViewIndexBinds()
    {
        $leaf = $this->createLeaf();

        $request = new WebRequest();
        $request->postData["Host_TestControl(1)_a"] = "123";
        $request->postData["Host_TestControl(1)_b"] = "234";

        $leaf->generateResponse($request);

        $this->assertEquals("123", $leaf->getModelForTesting()->TestControl["1"]["a"]);
        $this->assertEquals("234", $leaf->getModelForTesting()->TestControl["1"]["b"]);
    }
}

class Host extends Leaf
{

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return HostView::class;
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

class HostView extends View
{
    public static $control;

    protected function createSubLeaves()
    {
        parent::createSubLeaves();

        $this->registerSubLeaf(
            self::$control = new TestCompositeControl("TestControl")
        );
    }

    protected function printViewContent()
    {
    }
}

class TestCompositeControl extends CompositeControl
{
    /**
     * The place to parse the value property and break into the sub values for sub controls to bind to
     *
     * @param $compositeValue
     */
    protected function parseCompositeValue($compositeValue)
    {
        // TODO: Implement parseCompositeValue() method.
    }

    protected function getViewClass()
    {
        return TestCompositeControlView::class;
    }

    /**
     * The place to combine the model properties for sub values into a single value, array or object.
     *
     * @return mixed
     */
    protected function createCompositeValue()
    {
        return [
            "a" => $this->model->a,
            "b" => isset($this->model->b) ? $this->model->b : null
        ];
    }
}

class TestCompositeControlView extends ControlView
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(
            new TestControl2("a"),
            new TestControl2("b")
        );
    }
}

class TestControl2 extends Control
{
    protected function getViewClass()
    {
        return TestControlView2::class;
    }
}

class TestControlView2 extends ControlView
{
}