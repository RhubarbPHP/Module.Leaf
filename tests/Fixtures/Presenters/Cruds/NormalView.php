<?php
/**
 * Created by JetBrains PhpStorm.
 * User: scott
 * Date: 04/09/2013
 * Time: 09:18
 * To change this template use File | Settings | File Templates.
 */

namespace Rhubarb\Leaf\UnitTesting\Presenters\Cruds;


use Rhubarb\Leaf\Views\View;

class NormalView extends View
{
	public function printViewContent()
	{
		print "My New View";
	}
}