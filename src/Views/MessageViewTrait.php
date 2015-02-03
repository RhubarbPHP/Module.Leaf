<?php

namespace Rhubarb\Leaf\Views;

trait MessageViewTrait
{
	public $message = false;

	protected function OnBeforePrintViewContent()
	{
		$messages = $this->GetMessages();

		if ( isset( $messages[ $this->message ] ) )
		{
			$closure = $messages[ $this->message ];

			if ( is_callable( $closure ) )
			{
				print $closure();
			}
			else
			{
				print $closure;
			}

			return false;
		}
	}

	/**
	 * Should return an array of key value pairs storing message texts against an arbitrary tracking code.
	 *
	 * @return string[]
	 */
	protected abstract function GetMessages();
} 