<?php

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Tests\Fixtures\TestLeafModel;
use Rhubarb\Leaf\Views\View;

class StateTest extends LeafTestCase
{
    public function testLeafOutputsState()
    {
        $request = $this->getRequestWithPostData(
            [
                "LeafWithState_state" => json_encode(["forename" => "John"])
            ]
        );

        $this->leaf = $this->createLeaf();
        $model = TestLeafModel::getModel();

        $this->assertEquals("John", $model->forename);
    }

    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new LeafWithState();
    }
}

class LeafWithState extends Leaf
{

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return LeafWithStateView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new TestLeafModel();
    }
}

class LeafWithStateView extends View
{

}