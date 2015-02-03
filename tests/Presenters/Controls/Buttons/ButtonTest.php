<?php

namespace Rhubarb\Leaf\Presenters\Controls\Buttons;

use Rhubarb\Crown\UnitTesting\CoreTestCase;

/**
 *
 * @author acuthbert
 * @copyright GCD Technologies 2013
 */
class ButtonTest extends CoreTestCase
{
	public function testButtonPressedEventIsPassedThroughConstructor()
	{
		$trigger = false;

		$button = new Button( "TestButton", "Any Text", function() use ( &$trigger )
		{
			$trigger = true;
		});

		$button->simulateButtonPress();

		$this->assertTrue( $trigger );
	}
}