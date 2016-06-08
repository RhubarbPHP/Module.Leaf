<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Leaf\Views\View;

abstract class ControlView extends View
{
    protected $requiresContainerDiv = false;

    /**
     * @var ControlModel
     */
    protected $model;

    protected function parseRequest(WebRequest $request)
    {
        $path = $this->model->leafPath;

        // By default if a control can be represented by a single HTML element then the name of that element
        // should equal the leaf path of the control. If that is true then we can automatically discover and
        // update our model.

        $value = $request->post($path);
        if ($value !== null){
            $this->model->setValue($value);
        }

        // Now we search for indexed data. We can't unfortunately guess what the possible indexes are so we
        // have to use a regular expression to find and extract any indexes. Note that it's not possible to
        // have both un-indexed and indexed versions of the same leaf on the parent. In that case the indexed
        // version will create an array of model data in place of the single un-indexed value.
        $postData = $request->postData;

        foreach($postData as $key => $value){
            if (preg_match("/".$this->model->leafPath."\(([^)]+)\)$/", $key, $match)){
                $this->setControlValueForIndex($match[1], $value);
            }
        }
    }

    /**
     * An opportunity for the control to sanitise incoming posted data.
     * @param $value
     */
    protected function parsePostedValue($value)
    {
        return $value;
    }

    protected function setControlValueForIndex($index, $value)
    {
        $this->model->value = $this->parsePostedValue($value);
        $this->model->valueChangedEvent->raise($index);
    }

    protected function getNameValueClassAndAttributeString($includeValue = true)
    {
        $classes = $this->model->getClassAttribute();
        $otherAttributes = $this->model->getHtmlAttributes();

        $string = 'leaf-name="'.$this->model->leafName.'" name="'.$this->model->leafPath.'" id="'.$this->model->leafPath.'" '.$classes.$otherAttributes;

        if ($includeValue) {
            $string .= ' value="' . htmlentities($this->model->value) . '" ';
        }

        return $string;
    }
}
