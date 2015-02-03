<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\CheckSet;

use Rhubarb\Leaf\Presenters\Controls\Selection\Sets\SetSelectionControlView;

class CheckSetView extends SetSelectionControlView
{
	public function GetInputHtml( $name, $value, $item )
	{
		$checked = ( $this->IsValueSelected( $value ) ) ? ' checked="checked"' : '';

		return '<input type="checkbox" name="'.htmlentities( $name ).'[]" value="'.htmlentities( $value ).'" presenter-name="'.htmlentities( $this->presenterName ).'" id="'.htmlentities( $this->GetInputId( $name, $value ) ).'"'.$checked.'>';
	}

	public function GetItemOptionHtml( $value, $label, $item, $classSuffix = "" )
	{
		return parent::GetItemOptionHtml( $value, $label, $item, "checkbox" );
	}
}