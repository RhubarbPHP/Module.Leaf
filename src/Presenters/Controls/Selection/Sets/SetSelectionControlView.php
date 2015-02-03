<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\Sets;

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
	public function GetLabelHtml( $label, $inputId )
	{
		return '<label for="'.htmlentities( $inputId ).'">'.$label.'</label>';
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
	abstract public function GetInputHtml( $name, $value, $item );

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
	public function GetItemOptionHtml( $value, $label, $item, $classSuffix = "" )
	{
		$name = $this->presenterPath;
		$id = $this->GetInputId( $name, $value );

		$inputHtml = $this->GetInputHtml( $name, $value, $item, $id );


		return '<label>'.$inputHtml.'&nbsp;'.$label.'</label>';
	}

	public function GetInputId( $name, $value )
	{
		return $name.'-'.$value;
	}

	protected function printViewContent()
	{
		foreach( $this->_availableItems as $item )
		{
			print $this->GetItemOptionHtml( $item->value, $item->label, $item );
		}
	}
}