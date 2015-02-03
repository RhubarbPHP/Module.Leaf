<?php

namespace Rhubarb\Leaf\Presenters\Controls\Buttons;

require_once __DIR__."/../JQueryControlView.class.php";

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Presenters\Controls\JQueryControlView;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class ButtonView extends JQueryControlView implements IButtonView
{
	private $text;

	public $useXmlRpc = false;
	public $validator = null;
	public $validatorHostPresenterPath = "";

	private $_confirmMessage = "";
	private $_inputType = "submit";

	public function __construct()
	{
		$this->_requiresContainer = false;
		$this->_requiresStateInputs = false;
	}

	public function printViewContent()
	{
		$xmlAttribute = ( $this->useXmlRpc ) ? " xmlrpc=\"yes\"" : "";
		$validationAttribute = ( $this->validator != null ) ? " validation=\"".htmlentities( json_encode( $this->validator->GetJsonStructure() ) )."\"" : "";
		$validatorAttribute = ( $this->validatorHostPresenterPath ) ? " validator=\"".htmlentities( $this->validatorHostPresenterPath )."\"" : "";
		$confirmAttribute = ( $this->_confirmMessage != "" ) ? " confirm=\"".htmlentities( $this->_confirmMessage )."\"" : "";

		?>
		<input type="<?= $this->_inputType ?>" name="<?=htmlentities( $this->GetIndexedPresenterPath() );?>" presenter-name="<?=htmlentities( $this->presenterName );?>" id="<?=htmlentities( $this->GetIndexedPresenterPath() );?>" value="<?=htmlentities( $this->text );?>"<?= $this->GetClassTag().$this->GetHtmlAttributeTags().$xmlAttribute.$validationAttribute.$validatorAttribute.$confirmAttribute ?>/>
		<?php
	}

	public function SetButtonText( $text )
	{
		$this->text = $text;

		return $this;
	}

	public function SetButtonType( $type )
	{
		$this->_inputType = $type;

		return $this;
	}

	public function SetConfirmMessage( $confirmMessage )
	{
		$this->_confirmMessage = $confirmMessage;

		return $this;
	}

	protected function getClientSideViewBridgeName()
	{
		return "Button";
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/../../../../ClientSide/Resources/validation/validation.js";
		$package->resourcesToDeploy[] = __DIR__."/button.js";

		return $package;
	}
}
