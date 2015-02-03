<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\TextBox;

require_once __DIR__."/../../ControlPresenter.class.php";

use Rhubarb\Crown\Context;
use \Rhubarb\Leaf\Presenters\Controls\ControlPresenter;
use Rhubarb\Leaf\Presenters\PresenterModel;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class TextBox extends ControlPresenter
{
	protected $_size = 40;
	protected $_maxLength;
	protected $_allowBrowserAutoComplete = true;
	protected $_defaultValue = "";
	protected $_placeholderText = "";

	public function __construct( $name = "", $size = 40 )
	{
		parent::__construct( $name );

		$this->_size = $size;
	}

	/**
	 * @param string $defaultValue
	 */
	public function SetDefaultValue( $defaultValue )
	{
		$this->_defaultValue = $defaultValue;

		if ( !$this->Text )
		{
			$this->Text = $this->_defaultValue;
		}
	}

	/**
	 * @return string
	 */
	public function GetDefaultValue()
	{
		return $this->_defaultValue;
	}

	/**
	 * @param string $placeholderText
	 */
	public function SetPlaceholderText( $placeholderText )
	{
		$this->_placeholderText = $placeholderText;
	}

	/**
	 * @return string
	 */
	public function GetPlaceholderText()
	{
		return $this->_placeholderText;
	}

	protected function createView()
	{
		$view = new TextBoxView();
		$this->RegisterView( $view );
	}

	public function SetSize( $size )
	{
		$this->_size = $size;

		return $this;
	}

	public function SetMaxLength( $length )
	{
		$this->_maxLength = $length;

		return $this;
	}

	public function SetAllowBrowserAutoComplete( $allowBrowserAutoComplete )
	{
		$this->_allowBrowserAutoComplete = $allowBrowserAutoComplete;
	}

	protected function ApplyBoundData( $data )
	{
		if ( $data === null )
		{
			$data = $this->_defaultValue;
		}

		$this->model->Text = $data;
	}

	protected function ExtractBoundData()
	{
		return $this->model->Text;
	}

	protected function applyModelToView()
	{
		parent::applyModelToView();

		$this->view->SetText( $this->model->Text );
		$this->view->SetSize( $this->_size );
		$this->view->SetPlaceholderText( $this->_placeholderText );
		$this->view->SetAllowBrowserAutoComplete( $this->_allowBrowserAutoComplete );

		if ( $this->_maxLength )
		{
			$this->view->SetMaxLength( $this->_maxLength );
		}
	}

	protected function parseRequestForCommand()
	{
        $request = Context::CurrentRequest();
        $text = $request->Post( $this->getIndexedPresenterPath() );

		if ( $text !== null )
		{
			$this->model->Text = $text;
			$this->SetBoundData();
		}
	}

	protected function initialiseModel()
	{
		parent::initialiseModel();

		$this->model->Text = "";
	}
}
