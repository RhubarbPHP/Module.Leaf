<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\UrlHandlers;

use Rhubarb\Crown\Request\Request;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\UrlHandlers\CollectionUrlHandling;
use Rhubarb\Crown\UrlHandlers\UrlHandler;

class LeafCollectionUrlHandler extends UrlHandler
{
    use CollectionUrlHandling;

    protected $collectionPresenterClassName;
    protected $itemPresenterClassName;
    protected $additionalPresenterClassNameMap = [];
    protected $urlAction = "";

    /**
     * @param string $collectionPresenterClassName The full namespaced class name of the presenter representing the collection
     * @param string $itemPresenterClassName The full namespaced class name of the presenter representing an individual item
     * @param array $additionalPresenterClassNameMap An optional associative array mapping 'actions' to other presenters.
     * @param array $children
     */
    public function __construct(
        $collectionPresenterClassName,
        $itemPresenterClassName,
        $additionalPresenterClassNameMap = [],
        $children = []
    ) {
        parent::__construct($children);

        $this->collectionPresenterClassName = $collectionPresenterClassName;
        $this->itemPresenterClassName = $itemPresenterClassName;
        $this->additionalPresenterClassNameMap = $additionalPresenterClassNameMap;
    }

    /**
     * Should be implemented to return a true or false as to whether this handler supports the given request.
     *
     * Normally this involves testing the request URI.
     *
     * @param Request $request
     * @param string $currentUrlFragment
     * @return bool
     */
    protected function getMatchingUrlFragment(Request $request, $currentUrlFragment = "")
    {
        $uri = $currentUrlFragment;

        $parentResponse = parent::getMatchingUrlFragment($request, $currentUrlFragment);

        if (preg_match("|^" . $this->url . "([^/]+)/|", $uri, $match)) {
            if (isset($this->additionalPresenterClassNameMap[$match[1]])) {
                $this->urlAction = $match[1];
            } else {
                $this->resourceIdentifier = $match[1];
                $this->isCollection = false;
            }

            return $match[0];
        }

        return $parentResponse;
    }

    protected function getPresenterClassName()
    {
        $leafClass = false;

        if ($this->urlAction != "") {
            if (isset($this->additionalPresenterClassNameMap[$this->urlAction])) {
                $leafClass = $this->additionalPresenterClassNameMap[$this->urlAction];
            }
        }

        if ($leafClass === false) {
            if ($this->isCollection()) {
                $leafClass = $this->collectionPresenterClassName;
            } else {
                $leafClass = $this->itemPresenterClassName;
            }
        }

        return $leafClass;
    }

    /**
     * Return the response if appropriate or false if no response could be generated.
     *
     * @param mixed $request
     * @return bool
     */
    protected function generateResponseForRequest($request = null)
    {
        $leafClass = $this->getPresenterClassName();
        $leaf = new $leafClass();
        $leaf->itemIdentifier = $this->resourceIdentifier;

        $response = $leaf->generateResponse($request);

        if (is_string($response)) {
            $htmlResponse = new HtmlResponse($leaf);
            $htmlResponse->setContent($response);
            $response = $htmlResponse;
        }

        return $response;
    }
}
