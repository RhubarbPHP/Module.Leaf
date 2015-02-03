<?php

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__."/../../Core/Modelling/ModelState.class.php";

use Rhubarb\Stem\ModelState;

/**
 * A simple extension of Model to add some properties often used by presenters
 *
 * @property string $PresenterName	An optional name for a presenter
 * @property string $PresenterPath	The path within the hierarchy of sub presenters to identify this one.
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class PresenterModel extends ModelState
{

}
