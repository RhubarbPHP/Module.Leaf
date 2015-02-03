<?php

namespace Rhubarb\Leaf\UrlHandlers;

use Rhubarb\Stem\UnitTesting\User;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\UnitTesting\CoreTestCase;

class MvpRestHandlerTest extends CoreTestCase
{
	/**
	 * @var MvpRestHandler
	 */
	private $rest;

	protected function setUp()
	{
		parent::setUp();

		// Note that we're using any old presenters here. The proof is that they actually get selected for the response.
		$this->rest = new MvpRestHandler( "Rhubarb\Stem\UnitTesting\User",
			"Rhubarb\Leaf\UnitTesting\Presenters\Switched\Address",
			"Rhubarb\Leaf\UnitTesting\Presenters\Switched\Details",
			[ "add" => "Rhubarb\Leaf\UnitTesting\Presenters\Switched\Thanks" ]
		);

		$this->rest->SetUrl( "/users/" );
	}

	public function testRestHandlerInstantiatesCollectionView()
	{
		$request = new WebRequest();
		$request->UrlPath = "/users/";
		$request->Server( "HTTP_ACCEPT", "text/html" );
		$request->Server( "REQUEST_METHOD", "get" );

		$response = $this->rest->GenerateResponse( $request );
		$this->assertInstanceOf( "Rhubarb\Leaf\UnitTesting\Presenters\Switched\Address", $response->GetGenerator() );

		$mvp = $response->GetGenerator();

		$this->assertInstanceOf( "Rhubarb\Stem\Collections\Collection", $mvp->restCollection );
	}

	public function testRestHandlerInstantiatesModelView()
	{
		$user = new User();
		$user->Username = "smith";
		$user->Save();

		$request = new WebRequest();
		$request->UrlPath = "/users/1/";
		$request->Server( "HTTP_ACCEPT", "text/html" );
		$request->Server( "REQUEST_METHOD", "get" );

		$response = $this->rest->GenerateResponse( $request );
		$this->assertInstanceOf( "Rhubarb\Leaf\UnitTesting\Presenters\Switched\Details", $response->GetGenerator() );

		$mvp = $response->GetGenerator();

		$this->assertEquals( "smith", $mvp->restModel->Username );
	}

	public function testAdditionalPresentersConsidered()
	{
		$request = new WebRequest();
		$request->UrlPath = "/users/add/";
		$request->Server( "HTTP_ACCEPT", "text/html" );
		$request->Server( "REQUEST_METHOD", "get" );

		$response = $this->rest->GenerateResponse( $request );
		$this->assertInstanceOf( "Rhubarb\Leaf\UnitTesting\Presenters\Switched\Thanks", $response->GetGenerator() );
	}
}