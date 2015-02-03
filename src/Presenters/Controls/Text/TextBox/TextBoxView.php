<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\TextBox;

use \Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Presenters\PresenterDeploymentPackage;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class TextBoxView extends ControlView implements ITextBoxView
{
	protected $htmlType = "text";

	protected $_placeholderText = "";

	protected $_allowBrowserAutoComplete = true;

	public function __construct( $htmlType = "text" )
	{
		$this->htmlType = $htmlType;

		$this->_requiresContainer = false;
		$this->_requiresStateInputs = false;
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/TextBoxViewBridge.js";

		return $package;
	}

	protected function getClientSideViewBridgeName()
	{
		return "TextBoxViewBridge";
	}

	protected $text = "";

	public function SetText( $text )
	{
		$this->text = $text;
	}

	public function SetAllowBrowserAutoComplete( $allowBrowserAutoComplete )
	{
		$this->_allowBrowserAutoComplete = $allowBrowserAutoComplete;
	}

	/**
	 * @param string $placeholderText
	 */
	public function SetPlaceholderText( $placeholderText )
	{
		$this->_placeholderText = $placeholderText;
	}

	public function printViewContent()
	{
		$maxLength = ( $this->_maxLength ) ? "maxlength=\"".$this->_maxLength."\"" : "";
		$autoCompleteAttribute = ( !$this->_allowBrowserAutoComplete ) ? " autocomplete=\"off\"" : "";

		$placeholderText = $this->_placeholderText ? ' placeholder="'.\htmlentities( $this->_placeholderText ).'"' : "";
		?>
		<input type="<?=\htmlentities( $this->htmlType );?>" size="<?=$this->_size;?>" <?=$maxLength;?> name="<?=\htmlentities( $this->GetIndexedPresenterPath() );?>" value="<?=\htmlentities( $this->text );?>" id="<?=\htmlentities( $this->GetIndexedPresenterPath() );?>" presenter-name="<?=\htmlentities( $this->presenterName );?>"<?= $autoCompleteAttribute.$this->GetHtmlAttributeTags().$placeholderText.$this->GetClassTag() ?> />
		<?php
	}

	private $_size;

	public function SetSize( $size )
	{
		$this->_size = $size;
	}

	private $_maxLength;

	public function SetMaxLength( $length )
	{
		$this->_maxLength = $length;
	}

	public function GetSpawnSettings()
	{
		$settings = parent::GetSpawnSettings();
		$settings[ "size" ] = $this->_size;
		$settings[ "maxLength" ] = $this->_maxLength;
		$settings[ "allowBrowserAutoComplete" ] = $this->_allowBrowserAutoComplete;

		return $settings;
	}
}
