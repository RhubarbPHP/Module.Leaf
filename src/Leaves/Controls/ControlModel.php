<?php

namespace Rhubarb\Leaf\Leaves\Controls;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Leaves\LeafModel;

class ControlModel extends LeafModel
{
    /**
     * The controls current value
     *
     * @var string
     */
    public $value;

    /**
     * Raised when the value changes.
     *
     * @var Event
     */
    public $valueChangedEvent;

    public $cssClassNames = [];

    public $htmlAttributes = [];

    /**
     * Some auto layout features may ask a Control leaf to supply a label. If this property has
     * a value it will be used otherwise the controls name will be auto converted to Title Case.
     *
     * @see Control::getLabel()
     * @var string
     */
    public $label = "";

    public function __construct()
    {
        $this->valueChangedEvent = new Event();
    }

    public function addCssClassNames(...$classNames)
    {
        $this->cssClassNames = array_merge($this->cssClassNames, $classNames);
    }

    public function addHtmlAttribute($attributeName, $attributeValue)
    {
        $this->htmlAttributes[$attributeName] = $attributeValue;
    }

    public function getClassAttribute()
    {
        if (sizeof($this->cssClassNames)) {
            return " class=\"" . implode(" ", $this->cssClassNames) . "\"";
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

    /**
     * Should normally be called instead of setting $value directly.
     *
     * Raises the $valueChangedEvent.
     *
     * @param $value
     */
    public function setValue($value)
    {
        $this->value = $value;
        $this->valueChangedEvent->raise();
    }
}