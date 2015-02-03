<?php

namespace Rhubarb\Leaf\Presenters\Application\Tabs;

class TabDefinition
{
	public $label = "";

	public $data = [];

	public $selected = false;

	public function __construct( $label, $tabData = [] )
	{
		$this->label = $label;
		$this->data = $tabData;
		$this->selected = false;
	}
}