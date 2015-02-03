<?php

namespace Rhubarb\Leaf\Presenters\Controls\CheckBoxes;

require_once __DIR__."/../ControlView.class.php";

use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Views\SpawnableByViewBridgeViewTrait;
use Rhubarb\Leaf\Views\WithViewBridgeTrait;

class CheckBoxView extends ControlView
{
	use WithViewBridgeTrait;
	use SpawnableByViewBridgeViewTrait;

	private $checked = false;

	public function __construct()
	{
		$this->_requiresContainer = false;
		$this->_requiresStateInputs = false;
	}

	public function SetCheckedStatus( $checked )
	{
		$this->checked = $checked;
	}

	public function printViewContent()
	{
		$checked = ( $this->checked ) ? " checked=\"checked\"" : "";

		?>
		<input type="checkbox" value="1" name="<?=\htmlentities( $this->GetIndexedPresenterPath() );?>" id="<?=\htmlentities( $this->GetIndexedPresenterPath() );?>"<?=$checked;?> presenter-name="<?=\htmlentities( $this->presenterName );?>"<?= $this->GetHtmlAttributeTags().$this->GetClassTag() ?> />
		<?php
	}

	/**
	 * Implement this and return __DIR__ when your ViewBridge.js is in the same folder as your class
	 *
	 * @returns string Path to your ViewBridge.js file
	 */
	public function getDeploymentPackageDirectory()
	{
		return __DIR__;
	}

	public function GetSpawnSettings()
	{
		$settings = parent::GetSpawnSettings();
		$settings[ "Checked" ] = $this->checked;
		$settings[ "Attributes" ] = $this->htmlAttributes;

		return $settings;
	}
}