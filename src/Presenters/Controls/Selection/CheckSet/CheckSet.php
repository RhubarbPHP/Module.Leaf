<?php

namespace Rhubarb\Leaf\Presenters\Controls\Selection\CheckSet;

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;

class CheckSet extends DropDown
{
	protected function SupportsMultipleSelection()
	{
		return true;
	}

	protected function createView()
    {
        return new CheckSetView();
    }

	protected function parseRequestForCommand()
	{
		$request = Context::CurrentRequest();

		if ( $request->Server( "REQUEST_METHOD" ) == "POST" )
		{
			$values = $request->Post( $this->getIndexedPresenterPath() );

			if( $values === null )
			{
				$values = [];
			}

			if( !is_array( $values ) )
			{
				$values = explode( ",", $values );
			}

			$this->SetSelectedItems( $values );
			$this->SetBoundData();
		}
	}
}