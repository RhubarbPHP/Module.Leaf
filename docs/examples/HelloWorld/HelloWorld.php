<?php

namespace Rhubarb\Leaf\Examples\HelloWorld;

use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;

class HelloWorld extends Leaf
{
    /**
     * @var HelloWorldModel
     */
    protected $model;

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return HelloWorldView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new HelloWorldModel();
    }

    protected function onModelCreated()
    {
        parent::onModelCreated();

        // Attach event handlers to receive notices from the View
        $this->model->nameChangedEvent->attachHandler(function($newName){
            $this->model->name = $newName;
        });
    }
}