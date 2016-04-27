<?php

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\BindableLeafInterface;
use Rhubarb\Leaf\Leaves\BindableLeafTrait;
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
        $this->assertContains("Sub View", $response);
    }

    public function testSubLeavesWithBinding()
    {
        // In this example the sub leaf is bound to the "John" property of the parent model
        $model = SubLeafModel::getModel();
        $model->value = "John";
        $model->valueChangedEvent->raise();

        $response = $this->renderLeafAndGetContent();
        $this->assertContains("John", $response);
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

    protected function createSubLeaves()
    {
        $this->subLeaf = new SubLeaf("forename");
        $this->registerSubLeaf($this->subLeaf);
    }

    private $subLeaf;

    protected function printViewContent()
    {
        print $this->subLeaf;
        print $this->model->forename;
    }
}

class SubLeaf extends Leaf implements BindableLeafInterface
{
    use BindableLeafTrait;

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

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        $model = new SubLeafModel();

        $model->valueChangedEvent->attachHandler(function(){
            $this->bindingValueChangedEvent->raise();
        });

        return $model;
    }

    public function getBindingValue()
    {
        return $this->model->value;
    }

    public function setBindingValue($bindingValue)
    {
        $this->model->value = $bindingValue;
    }
}

class SubLeafView extends View
{
    protected function printViewContent()
    {
        print "Sub View";
    }
}

class SubLeafModel extends TestLeafModel
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