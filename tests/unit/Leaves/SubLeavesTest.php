<?php

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\BindableLeafInterface;
use Rhubarb\Leaf\Leaves\BindableLeafTrait;
use Rhubarb\Leaf\Leaves\Controls\Control;
use Rhubarb\Leaf\Leaves\Controls\ControlModel;
use Rhubarb\Leaf\Leaves\Controls\ControlView;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Tests\Fixtures\TestLeafModel;
use Rhubarb\Leaf\Views\View;

class SubLeavesTest extends LeafTestCase
{
    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new LeafWithSubLeaves();
    }

    public function testSubLeavesPrint()
    {
        $response = $this->renderLeafAndGetContent();
        $this->assertContains("name=forename", $response);
        $this->assertTrue($this->leaf->getModel()->isRootLeaf);
        $this->assertFalse($this->leaf->getView()->secondForename->getModel()->isRootLeaf);
        $this->assertContains("LeafWithSubLeaves_forenameState", $response);
    }

    public function testSubLeavesWithBinding()
    {
        $response = $this->renderLeafAndGetContent($this->getRequestWithPostData(["LeafWithSubLeaves_surname" => "Smith"]));
        $this->assertContains("Smith", $response);
    }

    public function testUniqueNames()
    {
        $response = $this->renderLeafAndGetContent();
        $this->assertContains("name=forename1", $response);

    }
}

class LeafWithSubLeaves extends Leaf
{
    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return LeafWithSubLeavesView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new LeafWithSubLeavesModel();
    }

    public function getModel()
    {
        return $this->model;
    }

    public function getView()
    {
        return $this->view;
    }
}

class LeafWithSubLeavesModel extends LeafModel
{
    public $forename;
    public $surname;
}

class LeafWithSubLeavesView extends View
{
    /**
     * @var LeafWithSubLeavesModel
     */
    protected $model;

    /**
     * Marked public for unit test analysis
     *
     * @var
     */
    public $secondForename;

    protected function createSubLeaves()
    {
        $this->registerSubLeaf(new SubLeaf("forename"));
        $this->registerSubLeaf(new SubLeaf("surname"));
        $this->registerSubLeaf($this->secondForename = new SubLeaf("forename"));
    }

    protected function printViewContent()
    {
        print $this->leaves["forename"];
        print $this->secondForename;
        print $this->model->surname;
    }
}

class SubLeaf extends Control
{
    /**
     * @var SubLeafModel
     */
    protected $model;

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return SubLeafView::class;
    }

    public function getModel()
    {
        return $this->model;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new SubLeafModel();
    }
}

class SubLeafView extends ControlView
{
    protected function printViewContent()
    {
        print "name=".$this->model->leafName;
    }
}

class SubLeafModel extends ControlModel
{
    public $value;

    /**
     * @var Event
     */
    public $valueChangedEvent;

    public function __construct()
    {
        parent::__construct();

        $this->valueChangedEvent = new Event();
    }
}
