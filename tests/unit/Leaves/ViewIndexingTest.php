<?php

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Leaf\Leaves\Controls\Control;
use Rhubarb\Leaf\Leaves\Controls\ControlView;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Views\View;

class ViewIndexingTest extends LeafTestCase
{
    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new ViewIndexTestLeaf();
    }

    public function testViewIndexedLeavesBind()
    {
        $request = $this->getRequestWithPostData(
            [
                "ViewIndexTestLeaf_Forename(test)" => "abc",
                "ViewIndexTestLeaf_Forename(6)" => "def",
            ]
        );

        $html = $this->renderLeafAndGetContent($request);
        $model = $this->leaf->getModel();

        $this->assertEquals("abc",$model->Forename["test"]);
        $this->assertContains("abc", $html);
    }
}

class ViewIndexTestLeaf extends Leaf
{
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
        return ViewIndexTestLeafView::class;
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

class ViewIndexTestLeafView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(new ViewIndexTestLeafSubLeaf("Forename"));
    }

    protected function printViewContent()
    {
        $this->leaves["Forename"]->printWithIndex("test");
        $this->leaves["Forename"]->printWithIndex(6);
    }
}

class ViewIndexTestLeafSubLeaf extends Control
{
    protected function getViewClass()
    {
        return ViewIndexTestLeafSubLeafView::class;
    }
}

class ViewIndexTestLeafSubLeafView extends ControlView
{
    protected function printViewContent()
    {
        print $this->model->value;
    }
}