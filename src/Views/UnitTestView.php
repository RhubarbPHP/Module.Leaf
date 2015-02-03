<?php

namespace Rhubarb\Leaf\Views;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class UnitTestView extends View
{
	public function SimulateEvent( $eventCode )
	{
		$args = func_get_args();

		return call_user_func_array( array( $this, "RaiseEvent" ), $args );
	}

	private $_setData = [];

	private $_methods = [];

	public function AttachMethod( $method, $callBack )
	{
		$this->_methods[ $method ] = $callBack;
	}

	public function GetData( $setterName )
	{
		if ( isset( $this->_setData[ $setterName ] ) )
		{
			return $this->_setData[ $setterName ];
		}

		return null;
	}

	public function __call( $method, $arguments )
	{
		if ( isset( $this->_methods[ $method ] ) )
		{
			call_user_func_array( $this->_methods[ $method ], $arguments );
		}

		if ( stripos( $method, "Set" ) === 0 )
		{
			$this->_setData[ substr( $method, 3 ) ] = $arguments[0];
		}
	}
}
