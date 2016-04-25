<?php

namespace Rhubarb\Leaf\Tests\Presenters\ Application\Table\Columns;

use Rhubarb\Leaf\Presenters\Application\Table\Columns\DateColumn;
use Rhubarb\Stem\Tests\Fixtures\Company;
use Rhubarb\Stem\Tests\Fixtures\ModelUnitTestCase;

class DateColumnTest extends ModelUnitTestCase
{
    public function testDateFormat()
    {
        $dateColumn = new DateColumn("InceptionDate");

        $company = new Company();
        $company->InceptionDate = "2012-01-01";

        $this->assertEquals("1st January 2012", $dateColumn->getCellContent($company, null));

        $company->InceptionDate = strtotime("2013-05-05");

        $this->assertEquals("5th May 2013", $dateColumn->getCellContent($company, null));

        $dateColumn = new DateColumn("InceptionDate", "Date", "d/m/Y");

        $company->InceptionDate = "2012-01-01";

        $this->assertEquals("01/01/2012", $dateColumn->getCellContent($company, null));
    }
}
