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

namespace Rhubarb\Leaf\Leaves;
use Rhubarb\Crown\Events\Event;

/**
 * The foundation of all model objects
 */
class LeafModel
{
    /**
     * @var string The name of the leaf
     */
    public $leafName;

    /**
     * @var string The full path hierarchy to uniquely identify the parent of this leaf
     */
    public $parentPath;

    /**
     * @var string The full path hierarchy to uniquely identify this leaf
     */
    public $leafPath;

    /**
     * The current leaf index. Null means no leaf index applies.
     *
     * @var mixed
     */
    public $leafIndex = null;

    /**
     * @var bool True if the view is the root leaf on the page.
     */
    public $isRootLeaf = true;

    /**
     * Raised when a View needs a sub leaf created for a given name.
     *
     * @var Event
     */
    public $createSubLeafFromNameEvent;

    public $cssClassNames = [];

    public $htmlAttributes = [];

    public function __construct()
    {
        $this->createSubLeafFromNameEvent = new Event();
    }

    /**
     * Returns an array of **publicly viewable** state data required to persist the state or provide state
     * information to a client side view bridge.
     *
     * @return string[] An associative array of state key value pair strings.
     */
    public function getState()
    {
        $state = get_object_vars($this);

        $publicState = [];
        foreach($this->getExposableModelProperties() as $property){
            if (isset($state[$property])) {
                $publicState[$property] = $state[$property];
            }
        }

        return $publicState;
    }

    /**
     * Called by Leaf after the leaf has parsed the request.
     */
    public function onAfterRequestSet()
    {

    }

    /**
     * Return the list of properties that can be exposed publically
     *
     * @return array
     */
    protected function getExposableModelProperties()
    {
        return ["leafName", "leafPath"];
    }

    /**
     * Restores the model from the passed state data.
     * @param string[] $stateData An associative array of state key value pair strings.
     */
    public function restoreFromState($stateData)
    {
        $publicProperties = $this->getExposableModelProperties();

        foreach($publicProperties as $key){
            if (isset($stateData[$key])){
                $this->$key = $stateData[$key];
            }
        }
    }

    public function addCssClassNames(...$classNames)
    {
        $this->cssClassNames = array_merge($this->cssClassNames, $classNames);
    }

    public function removeCssClassNames(...$classNames)
    {
        $this->cssClassNames = array_diff($this->cssClassNames, $classNames);
    }

    public function addHtmlAttribute($attributeName, $attributeValue)
    {
        $this->htmlAttributes[$attributeName] = $attributeValue;
    }

    public function removeHtmlAttribute($attributeName)
    {
        unset($this->htmlAttributes[$attributeName]);
    }

    public function getClassAttribute()
    {
        $classes = $this->cssClassNames;

        if ($this->isRootLeaf){
            $classes[] = "event-host";
        }

        if (sizeof($classes)) {
            return " class=\"" . implode(" ", $classes) . "\"";
        }

        return "";
    }

    public function getHtmlAttributes()
    {
        if (sizeof($this->htmlAttributes)) {
            $attributes = [];

            foreach ($this->htmlAttributes as $key => $value) {
                $attributes[] = $key . "=\"" . htmlentities($value) . "\"";
            }

            return " " . implode(" ", $attributes);
        }

        return "";
    }

    public function setBoundValue($propertyName, $value, $index = null)
    {
        if ($index !== null){
            if (!isset($this->$propertyName) || !is_array($this->$propertyName)){
                $this->$propertyName = [];
            }

            $array = &$this->$propertyName;
            $array[$index] = $value;
        } else {
            $this->$propertyName = $value;
        }
    }

    public function getBoundValue($propertyName, $index = null)
    {
        if ($index !== null ){
            $array = &$this->$propertyName;
            if (isset($array[$index])){
                return $array[$index];
            } else {
                return null;
            }
        } else {
            return isset($this->$propertyName) ? $this->$propertyName : null;
        }
    }
}
