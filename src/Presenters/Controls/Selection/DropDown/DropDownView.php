<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\DropDown;

require_once __DIR__."/../SelectionControlView.class.php";

use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlView;

class DropDownView extends SelectionControlView
{
	public function __construct()
	{
		$this->_requiresContainer = false;
		$this->_requiresStateInputs = false;
	}

	protected function getClientSideViewBridgeName()
	{
		return "DropDownViewBridge";
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/drop-down.js";

		return $package;
	}

	protected function printViewContent()
	{
		$name = $this->GetIndexedPresenterPath();

		if ( $this->_supportsMultiple )
		{
			$name .= "[]";
		}

		?>
		<select name="<?=\htmlentities( $name );?>" id="<?=\htmlentities( $this->GetIndexedPresenterPath() );?>" presenter-name="<?=\htmlentities( $this->presenterName );?>"<?= $this->GetHtmlAttributeTags().$this->GetClassTag() ?>>
<?php
		foreach( $this->_availableItems as $item )
		{
			$itemList = [ $item ];
			$isGroup = false;

			if ( isset( $item->Children ) )
			{
				$isGroup = true;
				$itemList = $item->Children;

				print "<optgroup label=\"".htmlentities( $item->label )."\">";
			}

			foreach( $itemList as $subItem )
			{
				$value = $subItem->value;
				$text = $subItem->label;

				$selected = ( $this->IsValueSelected( $value ) ) ? " selected=\"selected\"" : "";

                $data = json_encode( $subItem );

				print "<option value=\"".\htmlentities( $value )."\"".$selected." data-item=\"".htmlentities( $data )."\">".\htmlentities( $text )."</option>
";
			}

			if ( $isGroup )
			{
				print "</optgroup>";
			}
		}
?>
</select><?php
	}
}