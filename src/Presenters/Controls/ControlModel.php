<?php

namespace Rhubarb\Leaf\Presenters\Controls;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Presenters\PresenterModel;

class ControlModel extends PresenterModel
{
    public $value;

    /**
     * @var string[] An array of CSS class names to apply to the control
     */
    public $cssClassNames;

    /**
     * @var string[] An array of HTML attributes to apply to the control
     */
    public $htmlAttributes;

    /**
     * @var string A label used in control layout.
     */
    public $label = "";

    public function addCssClassNames($classNames = [])
    {
        $classes = $this->cssClassNames;

        if (!is_array($classes)) {
            $classes = [];
        }

        $classes = array_merge($classes, $classNames);
        $this->cssClassNames = $classes;
    }

    public function addCssClassName($className)
    {
        $this->addCssClassNames([$className]);
    }

    public function addHtmlAttribute($attributeName, $attributeValue)
    {
        $attributes = $this->htmlAttributes;

        if (!is_array($attributes)) {
            $attributes = [];
        }

        $attributes[$attributeName] = $attributeValue;

        $this->htmlAttributes = $attributes;
    }

    /**
     * Returns a label that the hosting view can use in the HTML output.
     *
     * @return string
     */
    public function getLabel()
    {
        if ($this->label != "") {
            return $this->label;
        }

        return StringTools::wordifyStringByUpperCase($this->getName());
    }
}