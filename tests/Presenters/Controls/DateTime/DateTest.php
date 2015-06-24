<?php

namespace Rhubarb\Leaf\Tests\Presenters\Controls\DateTime;

use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Controls\DateTime\Date;

class DateTest extends RhubarbTestCase
{
    public function testDatesAreFormattedDuringBinding()
    {
        $date = new UnitTestDate("TestDate");

        $date->PublicApplyBoundData(new \DateTime("2001-12-10 13:12:11"));

        $this->assertEquals("10/12/2001", $date->Text);

        $date->PublicApplyBoundData("2001-13-101 13:12:11");

        $this->assertEquals("", $date->Text);

        $date->Text = "23/04/2013";

        $this->assertEquals("2013-04-23", $date->PublicExtractBoundData());

        $date->Text = "abc234";

        $this->assertEquals("", $date->PublicExtractBoundData());
    }
}

class UnitTestDate extends Date
{
    public function PublicApplyBoundData($data)
    {
        $this->applyBoundData($data);
    }

    public function PublicExtractBoundData()
    {
        return $this->extractBoundData();
    }
}