<?php

namespace Rhubarb\Leaf\Tests\UrlHandlers;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\Address;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\Details;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\Thanks;
use Rhubarb\Leaf\UrlHandlers\MvpRestHandler;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Tests\Fixtures\User;

class MvpRestHandlerTest extends RhubarbTestCase
{
    /**
     * @var MvpRestHandler
     */
    private $rest;

    protected function setUp()
    {
        parent::setUp();

        // Note that we're using any old presenters here. The proof is that they actually get selected for the response.
        $this->rest = new MvpRestHandler(
            User::class,
            Address::class,
            Details::class,
            ["add" => Thanks::class]
        );

        $this->rest->setUrl("/users/");
    }

    public function testRestHandlerInstantiatesCollectionView()
    {
        $request = new WebRequest();
        $request->UrlPath = "/users/";
        $request->server("HTTP_ACCEPT", "text/html");
        $request->server("REQUEST_METHOD", "get");

        $response = $this->rest->generateResponse($request);
        $this->assertInstanceOf(Address::class, $response->getGenerator());

        $mvp = $response->getGenerator();

        $this->assertInstanceOf(Collection::class, $mvp->restCollection);
    }

    public function testRestHandlerInstantiatesModelView()
    {
        $user = new User();
        $user->Username = "smith";
        $user->save();

        $request = new WebRequest();
        $request->UrlPath = "/users/1/";
        $request->server("HTTP_ACCEPT", "text/html");
        $request->server("REQUEST_METHOD", "get");

        $response = $this->rest->generateResponse($request);
        $this->assertInstanceOf(Details::class, $response->getGenerator());

        $mvp = $response->getGenerator();

        $this->assertEquals("smith", $mvp->restModel->Username);
    }

    public function testAdditionalPresentersConsidered()
    {
        $request = new WebRequest();
        $request->UrlPath = "/users/add/";
        $request->server("HTTP_ACCEPT", "text/html");
        $request->server("REQUEST_METHOD", "get");

        $response = $this->rest->generateResponse($request);
        $this->assertInstanceOf(Thanks::class, $response->getGenerator());
    }
}
