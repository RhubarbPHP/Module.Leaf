<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scott
 * Date: 04/09/2013
 * Time: 09:09
 * To change this template use File | Settings | File Templates.
 */

namespace Rhubarb\Leaf\UnitTesting\Presenters\Cruds;


use Rhubarb\Leaf\Presenters\HtmlPresenter;

class NormalPresenter extends HtmlPresenter
{
	public function createView()
	{
		return new NormalView();
	}
}