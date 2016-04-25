<?php

namespace Rhubarb\Leaf\Tests\Presenters\ Application\Table\Columns;

use Rhubarb\Leaf\Presenters\Application\Table\Columns\BooleanColumn;
use Rhubarb\Stem\Tests\Fixtures\ModelUnitTestCase;
use Rhubarb\Stem\Tests\Fixtures\User;

class BooleanColumnTest extends ModelUnitTestCase
{
    public function testValueFormattedProperly()
    {
        $user = new User();
        $user->Active = 1;

        $bool = new BooleanColumn("Active");

        $this->assertEquals("Yes", $bool->getCellContent($user, null));

        $user->Active = 0;

        $this->assertEquals("No", $bool->getCellContent($user, null));
    }
}
