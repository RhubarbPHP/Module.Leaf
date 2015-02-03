<?php

namespace Rhubarb\Leaf\Presenters\Application\Table\Columns;

use Rhubarb\Stem\UnitTesting\Example;
use Rhubarb\Leaf\Presenters\Application\Table\Table;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Views\View;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class PresenterColumnTest extends CoreTestCase
{
	public function testColumnPresents()
	{
		Example::ClearObjectCache();

		$example = new Example();
		$example->Forename = "Andrew";
		$example->Save();

		$example = new Example();
		$example->Forename = "Bobby";
		$example->Save();

		$host = new HostPresenter();
		$output = $host->GenerateResponse();

		$this->assertContains( "id=\"_Forename(1)\"", $output );
		$this->assertContains( "value=\"Andrew\"", $output );
	}
}

class HostPresenter extends Presenter
{
	protected function createView()
	{
		return new HostView();
	}
}

class HostView extends View
{
	public $table;
	public $presented;

	public function createPresenters()
	{
		$this->addPresenters(
			$this->table = new Table( Example::Find() ),
			$this->presented = new TextBox( "Forename" )
		);

		$this->table->Columns =
		[
			"ContactID",
			new PresenterColumn( $this->presented, "Forename" )
		];
	}

	protected function printViewContent()
	{
		print $this->table;
	}


}