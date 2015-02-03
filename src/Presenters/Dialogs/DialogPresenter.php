<?php

namespace Rhubarb\Leaf\Presenters\Dialogs;

require_once __DIR__."/../HtmlPresenter.class.php";

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;

abstract class DialogPresenter extends HtmlPresenter
{
	use ModelProvider;

	public function __construct( $name = "" )
	{
		if ( $name == "" )
		{
			$name = str_replace( "Presenter", "", basename( str_replace( "\\", "/", get_class( $this ) ) ) );
		}

		parent::__construct($name);
	}

	public function SetPreferredWidth( $width )
	{
		$this->model->PreferredWidth = $width;
	}

	public function SetPreferredHeight( $height )
	{
		$this->model->PreferredHeight = $height;
	}

	protected function getPublicModelPropertyList()
	{
		$list = parent::getPublicModelPropertyList();
		$list[] = "PreferredWidth";
		$list[] = "PreferredHeight";

		return $list;
	}

	protected function OnResponseGenerated( $html )
	{
		$html = preg_replace( "|^<div id=|", "<div style=\"display: none\" class=\"dialog-container\" id=", $html );

		return $html;
	}

	protected function configureView()
	{
		parent::configureView();

		$this->view->attachEventHandler( "GetDialogData", function( $uniqueIdentifier )
		{
			return $this->GetDialogData( $uniqueIdentifier );
		});
	}

	/**
	 * Implement this function to support fetching existing data for the dialog.
	 *
	 * It is essential not to send back data which should be kept private. Remember the response to this
	 * function will be passed back to the client "in the clear" (SSL not withstanding)
	 *
	 * @param $uniqueIdentifier
	 * @return array
	 */
	protected function GetDialogData( $uniqueIdentifier )
	{
		return [];
	}
}