<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scott
 * Date: 29/08/2013
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */

namespace Rhubarb\Leaf\Presenters\Controls\Selection\RadioButtons;

use Rhubarb\Leaf\Presenters\Controls\Selection\Sets\SetSelectionControlView;

class RadioButtonsView extends SetSelectionControlView
{
	public function GetInputHtml( $name, $value, $item )
	{
		$checked = '';

		if( $this->IsValueSelected( $value ) )
		{
			$checked = ' checked="checked"';
		}

		return '<input type="radio" name="'.htmlentities( $name ).'" value="'.htmlentities( $value ).'" id="'.htmlentities( $this->GetInputId( $name, $value ) ).'"'.$checked.'>';
	}

	public function GetItemOptionHtml( $value, $label, $item, $classSuffix = "radio" )
	{
		return parent::GetItemOptionHtml( $value, $label, $item, $classSuffix );
	}
}