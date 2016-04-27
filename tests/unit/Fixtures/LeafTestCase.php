<?php

namespace Rhubarb\Leaf\Tests\Fixtures;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Tests\Fixtures\TestCases\RhubarbTestCase;
use Rhubarb\Leaf\Leaves\Leaf;

abstract class LeafTestCase extends RhubarbTestCase
{
    /**
     * @var Leaf
     */
    protected $leaf;

    protected function setUp()
    {
        parent::setUp();

        $this->leaf = $this->createLeaf();
    }

    protected function getRequestWithPostData($data)
    {
        $request = new WebRequest();

        foreach($data as $key => $value){
            $request->postData[$key] = $value;
        }

        return $request;
    }

    protected function getSimpleRequest()
    {
        return new WebRequest();
    }

    protected function renderLeafAndGetContent(WebRequest $request = null)
    {
        if (!$request){
            $request = $this->getSimpleRequest();
        }

        $response = $this->leaf->generateResponse($request);

        return $response->getContent();
    }

    /**
     * @return Leaf
     */
    protected abstract function createLeaf();
}