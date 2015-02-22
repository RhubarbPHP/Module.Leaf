<?php
/**
 * Created by JetBrains PhpStorm.
 * User: acuthbert
 * Date: 04/03/13
 * Time: 21:37
 * To change this template use File | Settings | File Templates.
 */

namespace Rhubarb\Leaf\Presenters\Application\Table\Columns;


use Rhubarb\Stem\Repositories\MySql\Schema\Columns\DateTime;
use Rhubarb\Stem\UnitTesting\Company;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class DateColumnTest extends CoreTestCase
{
	public function testDateFormat()
	{
		$dateColumn = new DateColumn( "InceptionDate" );

		$company = new Company();
		$company->InceptionDate = "2012-01-01";

		$this->assertEquals( "1st January 2012", $dateColumn->getCellContent( $company, null ) );

		$company->InceptionDate = strtotime( "2013-05-05" );

		$this->assertEquals( "5th May 2013", $dateColumn->getCellContent( $company, null ) );

		$dateColumn = new DateColumn( "InceptionDate", "Date", "d/m/Y" );

		$company->InceptionDate = "2012-01-01";

		$this->assertEquals( "01/01/2012", $dateColumn->getCellContent( $company, null ) );
	}
}
