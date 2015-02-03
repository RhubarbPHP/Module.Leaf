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

namespace Rhubarb\Leaf\Presenters\Controls\Selection\Sets;

require_once __DIR__ . '/../SelectionControlView.php';

use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlView;

abstract class SetSelectionControlView extends SelectionControlView
{
    /**
     * This gives us the opportunity to set up the HTML for the label on our set element.
     * Any customisation should be done here, rather than in the later executed GetRadioOptionHtml() method.
     *
     * @param $label   String A useful and front end appropriate message to describe the radio button choice
     * @param $inputId String This will be used in the "for" attribute
     *
     * @return string
     */
    public function getLabelHtml($label, $inputId)
    {
        return '<label for="' . htmlentities($inputId) . '">' . $label . '</label>';
    }

    /**
     * This gives us the opportunity to set up the HTML for the actual input.
     * It may be decided that it's best not to allow this functionality, and simply passing in
     * classes, ids etc. may be a more appropriate level of customisation.
     *
     * @param $name  String The raw value to be used in the HTML "name" attribute
     * @param $value String The raw value to be used in the HTML "value" attribute
     * @param $item
     * @return string
     */
    abstract public function getInputHtml($name, $value, $item);

    /**
     * Any "wrapping" of the input and label should take place here.
     * Manipulation of the label or input itself is not recommended as there are already
     * measures in place to allow for this (see GetLabelHtml() and GetInputHtml()).
     *
     * @param string $value String An already set up HTML string to represent an individual and ready to go radio button/check box etc
     * @param string $label A formatted label string with appropriate for attributes, classes and ids etc already set up
     * @param $item The full item we're generating the html for.
     * @param string $classSuffix An additional CSS class to be added to the class attribute
     *
     * @return string
     */
    public function getItemOptionHtml($value, $label, $item, $classSuffix = "")
    {
        $name = $this->presenterPath;
        $id = $this->getInputId($name, $value);

        $inputHtml = $this->getInputHtml($name, $value, $item, $id);


        return '<label>' . $inputHtml . '&nbsp;' . $label . '</label>';
    }

    public function getInputId($name, $value)
    {
        return $name . '-' . $value;
    }

    protected function printViewContent()
    {
        foreach ($this->availableItems as $item) {
            print $this->getItemOptionHtml($item->value, $item->label, $item);
        }
    }
}