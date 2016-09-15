<?php
/**
 * Copyright (c) 2016 RhubarbPHP.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Rhubarb\Leaf\Tests\Leaves;

use Rhubarb\Leaf\Controls\Common\Text\TextBox;
use Rhubarb\Leaf\Leaves\Leaf;
use Rhubarb\Leaf\Leaves\LeafModel;
use Rhubarb\Leaf\Tests\Fixtures\LeafTestCase;
use Rhubarb\Leaf\Views\View;

class BindingTest extends LeafTestCase
{
    public function testBindingSources()
    {
        /**
         * @var BindingLeafModel $model
         */
        $model = $this->leaf->getModelForTesting();

        $textBox = BindingLeafView::$textBox;
        $textBox->pushValue("abc123");

        $this->assertEquals("abc123", $model->Test);
        $this->assertEquals("abc123", $textBox->pullValue());

        $model->setSourceArray();

        $textBox->pushValue("def234");

        $this->assertEquals("def234", $model->testBindingSource["Test"]);
        $this->assertEquals("def234", $textBox->pullValue());

        $model->setSourceObject();
        $textBox->pushValue("fgh456");

        $this->assertEquals("fgh456", $model->testBindingSource->Test);
        $this->assertEquals("fgh456", $textBox->pullValue());
    }

    /**
     * @return Leaf
     */
    protected function createLeaf()
    {
        return new BindingLeaf();
    }
}

class BindingLeaf extends Leaf
{

    /**
     * @var BindingLeafModel
     */
    protected $model;

    /**
     * Returns the name of the standard view used for this leaf.
     *
     * @return string
     */
    protected function getViewClass()
    {
        return BindingLeafView::class;
    }

    /**
     * Should return a class that derives from LeafModel
     *
     * @return LeafModel
     */
    protected function createModel()
    {
        return new BindingLeafModel();
    }
}

class BindingLeafModel extends LeafModel
{
    public $testBindingSource;

    public function setSourceArray()
    {
        $this->testBindingSource = [];
        $this->bindingSource = &$this->testBindingSource;
    }

    public function setSourceObject()
    {
        $this->testBindingSource = new \stdClass();
        $this->bindingSource = $this->testBindingSource;
    }
}

class BindingTextBox extends TextBox
{
    public function pushValue($value)
    {
        $this->model->setValue($value);
    }

    public function pullValue()
    {
        return $this->getBindingValueRequestedEvent()->raise($this->model->leafIndex);
    }
}

class BindingLeafView extends View
{
    /**
     * @var BindingTextBox
     */
    public static $textBox;

    protected function createSubLeaves()
    {
        parent::createSubLeaves();

        self::$textBox = new BindingTextBox("Test");

        $this->registerSubLeaf(self::$textBox);
    }
}