<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\DropDown;

use Rhubarb\Crown\UnitTesting\CoreTestCase;

class DropDownTest extends CoreTestCase
{
	public function testDropDownReturnsHtmlCorrectly()
	{
		$dropDown = new DropDown( "Type" );
		$dropDown->SetSelectionItems( [ "a", [ "b", "bertie" ], "c" ] );

		$selectedItem = new \stdClass();
		$selectedItem->value = "b";

		$dropDown->model->SelectedItems = [ $selectedItem ];

		$html = $dropDown->GenerateResponse();

		$this->assertEquals( '		<select name="Type" id="Type" presenter-name="Type">
<option value="a" data-item="{&quot;value&quot;:&quot;a&quot;,&quot;label&quot;:&quot;a&quot;,&quot;data&quot;:[]}">a</option>
<option value="b" selected="selected" data-item="{&quot;value&quot;:&quot;b&quot;,&quot;label&quot;:&quot;bertie&quot;,&quot;data&quot;:[]}">bertie</option>
<option value="c" data-item="{&quot;value&quot;:&quot;c&quot;,&quot;label&quot;:&quot;c&quot;,&quot;data&quot;:[]}">c</option>
</select>', $html );
	}
}
