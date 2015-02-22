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

namespace Rhubarb\Leaf\Presenters\Controls\Selection\CheckSet;

require_once __DIR__ . '/../Sets/SetSelectionControlView.php';

use Rhubarb\Leaf\Presenters\Controls\Selection\Sets\SetSelectionControlView;

class CheckSetView extends SetSelectionControlView
{
	public function getInputHtml( $name, $value, $item )
	{
		$checked = ( $this->isValueSelected( $value ) ) ? ' checked="checked"' : '';

		return '<input type="checkbox" name="'.htmlentities( $name ).'[]" value="'.htmlentities( $value ).'" presenter-name="'.htmlentities( $this->presenterName ).'" id="'.htmlentities( $this->getInputId( $name, $value ) ).'"'.$checked.'>';
	}

	public function getItemOptionHtml( $value, $label, $item, $classSuffix = "" )
	{
		return parent::getItemOptionHtml( $value, $label, $item, "checkbox" );
	}
}