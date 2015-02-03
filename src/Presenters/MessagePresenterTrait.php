<?php

namespace Rhubarb\Leaf\Presenters;

trait MessagePresenterTrait
{
	protected $_message = false;

	protected function ActivateMessage( $message )
	{
		$this->_message = $message;
	}

	protected function OnModelAppliedToView()
	{
		$this->view->message = $this->_message;

		parent::OnModelAppliedToView();
	}
} 