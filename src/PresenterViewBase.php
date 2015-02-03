<?php

namespace Rhubarb\Crown\Mvp;

require_once __DIR__."/../Core/Events/EventEmitter.class.php";

use Rhubarb\Crown\Events\EventEmitter;

abstract class PresenterViewBase
{
	use EventEmitter;

	protected function ReceivedEventPassThrough()
	{
		$arguments = func_get_args();

		return call_user_func_array( [ $this, "RaiseEvent" ], $arguments );
	}
}
