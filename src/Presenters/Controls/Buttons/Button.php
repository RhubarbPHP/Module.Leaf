<?php

namespace Rhubarb\Leaf\Presenters\Controls\Buttons;

require_once __DIR__ . "/../JQueryControlView.class.php";

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Crown\ClientSide\Validation\ValidatorClientSide;
use Rhubarb\Crown\Context;
use Rhubarb\Crown\Deployment\ResourceDeploymentHandler;
use Rhubarb\Crown\Exceptions\ImplementationException;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;
use Rhubarb\Leaf\Presenters\Controls\JQueryControlView;
use Rhubarb\Crown\Request\Request;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class Button extends ControlPresenter
{
	private $_temporaryButtonText = "";

	private $_confirmMessage = "";

	private $_buttonType = "submit";

	public $validator = null;

	public $validatorHostPresenterPath = "";

	public $useXhr = false;

	public function __construct( $name = "", $buttonText = "", $onButtonPressed = null, $useXhr = false )
	{
		parent::__construct( $name );

		$this->AddCssClassName( "btn" );

		$this->_temporaryButtonText = $buttonText;
		$this->useXhr = $useXhr;

		$this->attachClientSidePresenterBridge = true;

		if ( $onButtonPressed != null )
		{
			if ( !is_callable( $onButtonPressed ) )
			{
				throw new ImplementationException( 'onButtonPressed must be callable.' );
			}

			$this->attachEventHandler( "ButtonPressed", $onButtonPressed );
		}
	}

	protected function initialiseModel()
	{
		parent::initialiseModel();

		if ( $this->model->ButtonText === null )
		{
			$this->SetButtonText( $this->_temporaryButtonText );
		}
	}

	public function SetButtonText( $buttonText )
	{
		$this->model->ButtonText = $buttonText;

		return $this;
	}

	public function SetValidator( ValidatorClientSide $validator )
	{
		$this->validator = $validator;

		return $this;
	}

	public function SetConfirmMessage( $confirmMessage )
	{
		$this->_confirmMessage = $confirmMessage;

		return $this;
	}

	public function SetButtonType( $type, $submitFormOnClick = false )
	{
		$this->_buttonType = $type;

		if ( $submitFormOnClick )
		{
			$this->AddCssClassName( "submit-on-click" );
		}

		return $this;
	}

	protected function parseRequestForCommand()
	{
        $request = $request = Context::CurrentRequest();
        $pushed = $request->Post( $this->getIndexedPresenterPath() );

		if ( $pushed != null )
		{
			$this->RaiseDelayedEvent( "ButtonPressed", $this->_viewIndex );
		}
	}

	/**
	 * Merely triggers the ButtonPressed event.
	 *
	 * Primarily used for unit testing.
	 */
	public function SimulateButtonPress()
	{
		$this->RaiseEvent( "ButtonPressed" );
	}

	protected function WrapOuter($html)
	{
		$name = $this->getName();

		if ( $name != "" )
		{
			$html = str_replace( "<input ", "<input presenter-name=\"".htmlentities( $name )."\"", $html );
		}

		return parent::WrapOuter($html);
	}

	protected function createView()
	{
		$view = new ButtonView();

		$this->RegisterView( $view );
	}

	protected function applyModelToView()
	{
		$this->view->SetButtonText( $this->model->ButtonText );
		$this->view->useXmlRpc = $this->useXhr;
		$this->view->validator = $this->validator;
		$this->view->validatorHostPresenterPath = $this->validatorHostPresenterPath;
		$this->view->SetConfirmMessage( $this->_confirmMessage );
		$this->view->SetButtonType( $this->_buttonType );

		parent::applyModelToView();
	}
}