<?php

namespace Rhubarb\Leaf\Presenters\Forms;

use Rhubarb\Stem\UnitTesting\Company;
use Rhubarb\Stem\UnitTesting\User;
use Rhubarb\Stem\UrlHandlers\ModelCollectionHandler;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\UrlHandlers\MvpRestHandler;
use Rhubarb\Leaf\Views\HtmlView;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class MvpRestBoundFormTest extends CoreTestCase
{
	public function testPresenterGetsRestModel()
	{
		$user = new User();
		$user->Username = "windychicken";
		$user->Save();

		$restHandler = new MvpRestHandler( "Rhubarb\Stem\UnitTesting\User", "", "Rhubarb\Leaf\Presenters\Forms\ModelBoundTestForm" );
		$restHandler->SetUrl( "/users/" );

		$request = new WebRequest();
		$request->UrlPath = "/users/".$user->UserID."/";
		$request->Server( "HTTP_ACCEPT", "text/html" );
		$request->Server( "REQUEST_METHOD", "get" );

		$response = $restHandler->generateResponse( $request );

		$mvp = $response->GetGenerator();
		$restModel = $mvp->GetRestModel();
		$restCollection = $mvp->GetRestCollection();

		$this->assertEquals( $user->Username, $restModel->Username );

		// Make sure the presenter model is left alone
		$this->assertEquals( 3, $mvp->model->NewValue );
		$this->assertNull( $restModel->NewValue );

		$mvp->PublicSetDataFromPresenter( "Test", "TestValue" );

		$this->assertEquals( "TestValue", $mvp->model->Test );
		$this->assertEquals( "TestValue", $restModel->Test );
	}

	public function testControlCreation()
	{
		$mvp = new ModelBoundTestForm();
		$mvp->setRestModel( new User() );

		$control = $mvp->PublicCreatePresenterByName( "NonExistant" );

		$this->assertNull( $control );

		$control = $mvp->PublicCreatePresenterByName( "Username" );

		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox", $control );

		$control = $mvp->PublicCreatePresenterByName( "Password" );

		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Controls\Text\Password\Password", $control );

		$control = $mvp->PublicCreatePresenterByName( "Active" );

		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Controls\CheckBoxes\CheckBox", $control );

		$control = $mvp->PublicCreatePresenterByName( "UserType" );

		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown", $control );

		$items = $control->getSelectionItems();

		$this->assertEquals( [ "", "Please Select" ], $items[0] );
		$this->assertInstanceOf( "Rhubarb\Stem\Repositories\MySql\Schema\Columns\Enum", $items[1] );

		$control = $mvp->PublicCreatePresenterByName( "CompanyID" );

		$this->assertInstanceOf( "Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown", $control );

		$items = $control->getSelectionItems();

		$this->assertEquals( [ "", "Please Select" ], $items[0] );
		$this->assertInstanceOf( "Rhubarb\Stem\Collections\Collection", $items[1] );
	}
}

class ModelBoundTestForm extends MvpRestBoundForm
{
	public function PublicSetDataFromPresenter( $dataKey, $value )
	{
		$this->setDataFromPresenter( $dataKey, $value );
	}

	public function PublicCreatePresenterByName( $presenterName )
	{
		return $this->createPresenterByName( $presenterName );
	}

	protected function initialiseModel()
	{
		$this->model->NewValue = 3;
	}

	protected function createView()
	{
		return new HtmlView();
	}
}