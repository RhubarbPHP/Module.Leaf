<?php

namespace Rhubarb\Leaf\Presenters\Application\Table\Columns;

use Rhubarb\Stem\Schema\Columns\Boolean;
use Rhubarb\Stem\UnitTesting\User;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class BooleanColumnTest extends CoreTestCase
{
	public function testValueFormattedProperly()
	{
		$user = new User();
		$user->Active = 1;

		$bool = new BooleanColumn( "Active" );

		$this->assertEquals( "Yes", $bool->getCellContent( $user, null ) );

		$user->Active = 0;

		$this->assertEquals( "No", $bool->getCellContent( $user, null ) );
	}
}
