<?php

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Tests\Fixtures\TestLeafModel;
use Rhubarb\Leaf\Views\View;

class StateTest extends LeafTestCase
{
    public function testLeafRestoresState()
    {
        $request = $this->getRequestWithPostData(
            [
                "LeafWithStateState" => json_encode(["forename" => "John"])
            ]
        );

        $this->leaf = $this->createLeaf();
        $this->leaf->setWebRequest($request);

        $model = TestLeafModel::getModel();

        $this->assertEquals("John", $model->forename);
    }

    public function testLeafOutputsState()
    {
        $response = $this->renderLeafAndGetContent();

        $this->assertContains("LeafWithStateState", $response);
        $this->assertContains(':&quot;Billy&quot;', $response);
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
        return new LeafWithStateModel();
    }
}

class LeafWithStateView extends View
{
    protected function createSubLeaves()
    {
        $this->registerSubLeaf(new SubLeaf2());
    }

    protected function printViewContent()
    {
        print $this->leaves["SubLeaf2"];
    }
}

class LeafWithStateModel extends TestLeafModel
{
    public $forename = "Billy";

    protected function getExposableModelProperties()
    {
        $list = parent::getExposableModelProperties();
        $list[] = "forename";

        return $list;
    }
}


class SubLeaf2 extends Leaf
{

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return SubLeafView2::class;
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

class SubLeafView2 extends View
{
    protected function printViewContent()
    {
        print "SubLeafView";
    }
}