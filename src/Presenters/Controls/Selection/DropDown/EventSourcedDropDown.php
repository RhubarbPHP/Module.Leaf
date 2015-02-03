<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\DropDown;

require_once __DIR__."/DropDown.class.php";

class EventSourcedDropDown extends DropDown
{
	protected function GetCurrentlyAvailableSelectionItems()
	{
		return $this->RaiseEvent( "GetCurrentlyAvailableSelectionItems" );
	}
}