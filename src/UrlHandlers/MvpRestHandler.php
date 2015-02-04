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
use Rhubarb\Crown\RestApi\Exceptions\RestImplementationException;
use Rhubarb\Stem\UrlHandlers\ModelCollectionHandler;

/**
 * A rest handler that handles HTML requests by passing control to MVP presenters.
 */
class MvpRestHandler extends ModelCollectionHandler
{
    protected $collectionPresenterClassName;
    protected $itemPresenterClassName;
    protected $additionalPresenterClassNameMap = [];
    protected $urlAction = "";

    /**
     * @param string $modelName The full namespaced class name of the model providing data for the requests
     * @param string $collectionPresenterClassName The full namespaced class name of the presenter representing the model collection
     * @param string $itemPresenterClassName The full namespaced class name of the presenter representing an individual item
     * @param array $additionalPresenterClassNameMap An optional associative array mapping 'actions' to other presenters.
     * @param array $children
     */
    public function __construct(
        $modelName,
        $collectionPresenterClassName,
        $itemPresenterClassName,
        $additionalPresenterClassNameMap = [],
        $children = []
    ) {
        parent::__construct($modelName, $children);

        $this->collectionPresenterClassName = $collectionPresenterClassName;
        $this->itemPresenterClassName = $itemPresenterClassName;
        $this->additionalPresenterClassNameMap = $additionalPresenterClassNameMap;
    }

    protected function getSupportedMimeTypes()
    {
        $mime = parent::getSupportedMimeTypes();

        $mime["application/core"] = "mvp";

        return $mime;
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
            if (is_numeric($match[1]) || isset($this->additionalPresenterClassNameMap[$match[1]])) {
                $this->urlAction = $match[1];
                $this->isCollection = false;

                return $match[0];
            }
        }

        return $parentResponse;
    }

    protected function getPresenterClassName()
    {
        $mvpClass = false;

        if ($this->urlAction != "") {
            if (isset($this->additionalPresenterClassNameMap[$this->urlAction])) {
                $mvpClass = $this->additionalPresenterClassNameMap[$this->urlAction];
            } else {
                if (is_numeric($this->urlAction)) {
                    $this->isCollection = false;
                }
            }
        }

        if ($mvpClass === false) {
            if ($this->isCollection()) {
                $mvpClass = $this->collectionPresenterClassName;
            } else {
                $mvpClass = $this->itemPresenterClassName;
            }
        }

        return $mvpClass;
    }

    /**
     * Return the response if appropriate or false if no response could be generated.
     *
     * @param mixed $request
     * @return bool
     */
    protected function generateResponseForRequest($request = null)
    {
        $mvpClass = $this->getPresenterClassName();
        $mvp = new $mvpClass();

        if ($this->isCollection()) {
            if (method_exists($mvp, "setRestCollection")) {
                try {
                    call_user_func(array($mvp, "setRestCollection"), $this->getModelCollection());
                } catch (RestImplementationException $er) {

                }
            }
        } else {
            if (method_exists($mvp, "setRestModel")) {
                try {
                    call_user_func(array($mvp, "setRestModel"), $this->getModelObject());
                } catch (RestImplementationException $er) {
                }
            }
        }

        $response = $mvp->generateResponse($request);

        if (is_string($response)) {
            $htmlResponse = new HtmlResponse($mvp);
            $htmlResponse->setContent($response);
            $response = $htmlResponse;
        }

        return $response;
    }
}