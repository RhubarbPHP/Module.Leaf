<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scott
 * Date: 29/08/2013
 * Time: 15:15
 * To change this template use File | Settings | File Templates.
 */

namespace Rhubarb\Leaf\Presenters\Controls\Selection\RadioButtons;


use Rhubarb\Leaf\Presenters\Controls\Selection\SelectionControlPresenter;

class RadioButtons extends SelectionControlPresenter
{
	protected function createView()
	{
		return new RadioButtonsView();
	}

	/**
	 * Call this from a hosting view to get a single radio button presented.
	 *
	 * Used when a custom layout of radio buttons is required.
	 *
	 * @param $value
	 * @return mixed
	 */
	public function GetIndividualRadioButtonHtml( $value )
	{
		$this->FetchBoundData();
		$this->applyModelToView();
		$this->beforeRenderView();

		return $this->view->GetInputHtml( $this->model->PresenterPath, $value, null );
	}
}