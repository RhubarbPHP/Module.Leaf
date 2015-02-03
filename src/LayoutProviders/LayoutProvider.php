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

namespace Rhubarb\Leaf\LayoutProviders;

/**
 * A layout provider translates a data structure of text and objects into an HTML structure.
 *
 * This allows for developers to control the content of a form while the design team can
 * radically change the organisation and appearance of the output through a dependency injection.
 *
 * LayoutProviders can either be created with an array of items, or the items can be passed
 * when printing. e.g.
 *
 * Method 1:
 *
 * $layout = LayoutProvider::GetDefaultLayoutProvider();
 * $layout->PrintItems(
 *      [
 *          "Forename",
 *          "Surname"
 *      ] );
 *
 * Method 2:
 *
 * $layout = LayoutProvider::GetDefaultLayoutProvider(
 *      [
 *          "Forename",
 *          "Surname"
 *      ] );
 *
 * print $layout;
 *
 */
abstract class LayoutProvider
{
    protected $items = [];

    public function __construct($items = [])
    {
        $this->items = $items;
    }

    private static $defaultLayoutProviderClassName = '\Rhubarb\Leaf\LayoutProviders\FieldSetWithLabelsLayoutProvider';

    public static function setDefaultLayoutProviderClassName($defaultLayoutProviderClassName)
    {
        self::$defaultLayoutProviderClassName = $defaultLayoutProviderClassName;
    }

    /**
     * Creates an instance of the default layout provider.
     *
     * @param array $items Optionally an array of items to print if using the toString approach.
     * @return LayoutProvider
     */
    public static function getDefaultLayoutProvider($items = [])
    {
        $provider = self::$defaultLayoutProviderClassName;

        return new $provider();
    }

    /**
     * Prints the layout surrounded by a container with a title or legend.
     *
     * @param string $containerTitle
     * @param mixed[] $items
     */
    public abstract function printItemsWithContainer($containerTitle, $items = null);

    public abstract function printContainerTitle($containerTitle);

    /**
     * Prints the items in a layout.
     *
     * @param $items
     */
    public function printItems($items = null)
    {
        $args = func_get_args();

        if (count($args) == 0) {
            $args = $this->items;
        }

        for ($i = 0; $i < sizeof($args); $i++) {
            $data = $args[$i];

            if (is_array($data)) {
                $this->printLabelValuePairs($data);
            } elseif (is_object($data)) {
                print $data;
            } else {
                $value = $this->generateValue($data);

                if ($value !== false && $value !== null) {
                    print $value;
                } else {
                    print $this->parseStringAsTemplate($data);
                }
            }
        }
    }

    /**
     * Scan the string for {} placeholders that might contain input names.
     *
     * @param $data
     * @return string
     */
    protected function parseStringAsTemplate($data)
    {
        $t = $html = $data;

        while (preg_match("/[{]([^{}]+)[}]/", $t, $regs)) {
            $t = str_replace($regs[0], "", $t);

            $input = $this->generateValue($regs[1]);

            if ($input !== null && $input !== false && $input !== $regs[1]) {
                $html = str_replace($regs[0], (string)$input, $html);
            }
        }

        return $html;
    }

    /**
     * Prints an array of label to value pairs.
     *
     * @param $pairs
     */
    public abstract function printLabelValuePairs($pairs);

    /**
     * Prints a single item
     *
     * @param $value
     * @param $label
     */
    public abstract function printValueWithLabel($value, $label);


    /**
     * Prints the content of a label for an item
     *
     * @param $label
     */
    public abstract function printLabel($label);

    /**
     * Prints the value or presenter
     *
     * @param $value
     */
    public abstract function printValue($value);

    public function __toString()
    {
        ob_start();

        $this->printItems($this->items);

        return ob_get_clean();
    }

    /**
     * @var Callback
     */
    private $valueGenerationCallBack = null;

    /**
     * Sets the callback function to use when a value needs generated
     *
     * @param Callback $callback
     */
    public function setValueGenerationCallBack($callback)
    {
        $this->valueGenerationCallBack = $callback;
    }

    protected function generateValue($valueName)
    {
        $newValue = null;

        if (isset($this->valueGenerationCallBack)) {
            $callBack = $this->valueGenerationCallBack;
            $newValue = $callBack($valueName);
        }

        if ($newValue !== null && $newValue !== false) {
            return $newValue;
        } else {
            return $this->parseStringAsTemplate($valueName);
        }
    }

    /**
     * @var Callback
     */
    private $validationPlaceholderGenerationCallBack = null;

    /**
     * Sets the callback function to use when a validation placeholder needs generated
     *
     * @param Callback $callback
     */
    public function setValidationPlaceholderGenerationCallBack($callback)
    {
        $this->validationPlaceholderGenerationCallBack = $callback;
    }

    /**
     * Asks the callback to generate a placeholder.
     *
     * @param $placeholderName
     * @return bool|null
     */
    protected function generatePlaceholder($placeholderName)
    {
        $placeholder = null;

        if (isset($this->validationPlaceholderGenerationCallBack)) {
            $callBack = $this->validationPlaceholderGenerationCallBack;
            $placeholder = $callBack($placeholderName);
        }

        if ($placeholder !== null && $placeholder !== false) {
            return $placeholder;
        }

        return false;
    }
}