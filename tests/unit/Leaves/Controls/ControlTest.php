<?php

namespace Rhubarb\Leaf\Tests\Leaves\Controls;

use Rhubarb\Leaf\Leaves\Controls\Control;
use Rhubarb\Leaf\Leaves\Controls\ControlModel;
use Rhubarb\Leaf\Leaves\Controls\ControlView;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;

class ControlTest extends LeafTestCase
{
    public function testControlRecognisesValueChange()
    {
        $request = $this->getRequestWithPostData([
            "TestControl" => "TestValue"
        ]);

        $this->leaf = new TestControl();
        $this->leaf->setWebRequest($request);

        $this->assertEquals("TestValue", $this->leaf->getModel()->value);
    }

    public function testSubControlDataBinds()
    {
        $request = $this->getRequestWithPostData([
            "TestControl_Test" => "AnotherTestValue"
        ]);

        $this->leaf = new TestControl();
        $this->leaf->setWebRequest($request);

        $this->assertEquals("AnotherTestValue", $this->leaf->getModel()->Test);
    }

    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new TestControl();
    }
}

class TestControl extends Control
{
    /**
     * @return ControlModel
     */
    public function getModel()
    {
        return $this->model;
    }


    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return TestControlView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new ControlModel();
    }
}

class TestControlView extends ControlView
{
    public static $subControl;

    protected function createSubLeaves()
    {
        parent::createSubLeaves();

        $this->registerSubLeaf(
            self::$subControl = new TestSubControl("Test")
        );
    }
}

class TestSubControl extends Control
{
    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return TestSubControlView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new ControlModel();
    }
}

class TestSubControlView extends ControlView
{

}