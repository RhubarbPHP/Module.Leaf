<?php

namespace Rhubarb\Leaf\Presenters\Controls;

require_once __DIR__."/../../Views/HtmlView.class.php";

use Rhubarb\Leaf\Views\HtmlView;
use Rhubarb\Leaf\Views\SpawnableByViewBridgeView;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class ControlView extends SpawnableByViewBridgeView
{
	public $cssClassNames = [];
	public $htmlAttributes = [];

	protected function GetClassTag()
	{
		if ( sizeof( $this->cssClassNames ) )
		{
			return " class=\"".implode( " ", $this->cssClassNames )."\"";
		}

		return "";
	}

	protected function GetHtmlAttributeTags()
	{
		if ( sizeof( $this->htmlAttributes ) )
		{
			$attributes = [];

			foreach( $this->htmlAttributes as $key => $value )
			{
				$attributes[] = $key."=\"".htmlentities( $value )."\"";
			}

			return " ".implode( " ", $attributes );
		}

		return "";
	}
}
