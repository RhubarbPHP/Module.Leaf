<?php

namespace Rhubarb\Leaf\Presenters\Controls;

use Rhubarb\Leaf\Presenters\ModelProvider;

/** 
 * 
 *
 * @package Rhubarb\Leaf\Presenters\Controls
 * @author      acuthbert
 * @copyright   2013 GCD Technologies Ltd.
 */
class CompositeControlPresenter extends ControlPresenter
{
	use ModelProvider;

	protected function OnModelUpdatedFromSubPresenter()
	{
		$this->SetBoundData();
	}
}