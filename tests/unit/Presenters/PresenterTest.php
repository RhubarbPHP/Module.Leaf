<?php

namespace Rhubarb\Leaf\Tests\Presenters;

use Rhubarb\Crown\Layout\LayoutModule;
use Rhubarb\Crown\Module;
use Rhubarb\Crown\Request\Request;
use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Tests\Fixtures\TestCases\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;
use Rhubarb\Leaf\Presenters\Presenter;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Simple;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\DetailsView;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\UnitTestSwitchedPresenter;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\Switched\UnitTestTextBox;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\TestViewIndexPresenter;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\UnitTestStatefulPresenter;
use Rhubarb\Leaf\Tests\Fixtures\Presenters\UnitTestView;

class PresenterTest extends RhubarbTestCase
{
    private $request;

    protected function setUp()
    {
        parent::setUp();

        $this->request = new WebRequest();
        $this->application->setCurrentRequest($this->request);
    }

    public function testPresenterFoundAndCorrectHtmlReturned()
    {
        LayoutModule::DisableLayout();
        $request = Request::current();

        // Simulate an incoming apache request
        $request->urlPath = "/simple/";
        $request->isWebRequest = true;

        $response = $this->application->generateResponseForRequest($request);

        $this->assertEquals("Don't change this content - it should match the unit test.", $response->getContent());

        LayoutModule::enableLayout();
    }

    public function testPresenterHasName()
    {
        $simple = new Simple();

        $this->assertEquals("Simple", $simple->getName());
    }

    public function testPresenterSupportsMultipleSubPrsentersWithTheSameName()
    {
        $simple = new Simple();

        // Simple has two sub presenters both called forename - let's check they have unique ids
        $simple->generateResponse();

        $subPresenters = $simple->getSubPresenters();

        $this->assertCount(2, $subPresenters);

        $this->assertEquals("Simple_Forename", $subPresenters["Forename"]->PresenterPath);
        $this->assertEquals("Simple_Forename2", $subPresenters["Forename2"]->PresenterPath);

    }

    public function testPresenterHandlesCommand()
    {
        $simple = new Simple();
        $simple->dispatchCommand("UpdateText");

        $html = $simple->generateResponse();

        $this->assertEquals("The text has changed!", $html);
    }

    public function testPresenterHandlesCommandWithParameter()
    {
        $simple = new Simple();
        $simple->dispatchCommand("UpdateText", "Some param text");

        $html = $simple->generateResponse();

        $this->assertEquals("Some param text", $html);
    }

    public function testPresenterCanBePrintedWithToString()
    {
        $simple = new Simple();
        $simple->dispatchCommand("UpdateText", "Some param text");

        $html = (string)$simple;

        $this->assertEquals("Some param text", $html);
    }

    public function testDelayedEventRunsAfterOtherEvents()
    {
        $simple = new Simple();

        // Simple has two events. The first is delayed, the second isn't.
        // We should see that the last event to run was actually the first event.

        $simple->generateResponse(Request::current());

        $this->assertEquals("FirstEvent", $simple->lastEventProcessed);
    }

    /**
     * Tests to make sure the presenter can persist it's model across posts.
     */
    public function testStateStorage()
    {
        $simple = new UnitTestStatefulPresenter();

        // To simulate the posting of state data we're going to encode the state and set it in our
        // post data for next instance of it to pick up.
        $state = '{"TestValue":"abc123"}';

        $request = Request::current();
        $request->postData[$simple->getPresenterPath() . "State"] = $state;

        $simple = new UnitTestStatefulPresenter();
        $simple->initialise();

        $this->assertEquals("abc123", $simple->model->TestValue);
    }

    public function testDataBinding()
    {
        $request = Request::current();
        // Test model binding 'get'
        $host = new UnitTestSwitchedPresenter();

        $html = $host->generateResponse($request);

        // The switched presenters model has Forename initialised to John. This test make sures that
        // the text box on the details presenter shows John.
        $this->assertEquals("John", UnitTestTextBox::$textBoxValue);

        // Test model binding 'set'
        $request = Request::current();
        $request->postData["UnitTestSwitchedPresenter_Details_Forename"] = "Jeremy";

        $host = new UnitTestSwitchedPresenter();

        $host->generateResponse($request);

        $this->assertEquals("Jeremy", $host->model->Forename);
    }

    /*
     * Suspended while validation is in flux
    public function testPresenterValidates()
    {
        $simple = new Simple();
        $simple->Forename = "";
        $simple->Surname = "";

        $validator = new Validator();
        $validator->validations[] = new HasValue( "Forename" );
        $validator->validations[] = new HasValue( "Surname" );

        $result = $simple->validate( $validator );

        $this->assertFalse( $result );

        // Check that we can get the validation error.
        $errors = $simple->getValidationErrorsByName( "Forename" );

        $this->assertCount( 1, $errors );
        $this->assertInstanceOf( "Rhubarb\Stem\Models\Validation\ValidationError", $errors[0] );
    }
    */

    public function testPresenterMarkedAsConfigured()
    {
        $presenter = new Simple();
        $presenter->removeEventHandlers();

        $this->assertFalse($presenter->isConfigured());

        $presenter->ModelSetting = "abc";

        $this->assertFalse($presenter->isConfigured());

        $presenter->ConfiguredSetting = "abc";

        $this->assertTrue($presenter->isConfigured());
    }

    public function testPresenterGetsChangedModels()
    {
        SubPresenterTest::$hosted = new Hosted("Goats");
        SubPresenterTest::$hostedView = new UnitTestView();

        $presenter = new Host();
        $presenter->generateResponse();

        $hosted = SubPresenterTest::$hosted;
        $hosted->NumberOfGoats = 999;

        $models = $presenter->getChangedPresenterModels();

        $this->assertCount(1, $models);
        $this->assertArrayHasKey("Host_Goats", $models);
        $this->assertEquals(999, $models["Host_Goats"]["NumberOfGoats"]);
    }

    public function testDisplayWithIndex()
    {
        $host = new UnitTestSwitchedPresenter();
        $host->test();

        $presenter = DetailsView::$forename;

        ob_start();

        $host->Forename = [4 => "def"];
        $presenter->displayWithIndex("4");

        $content = ob_get_clean();

        $this->assertContains("Forename(4)\"", $content);
        $this->assertContains("value=\"def\"", $content);

        $host = new TestViewIndexPresenter();
        $host->test();
        $content = $host->generateResponse();

        $this->assertContains("Test(0)_Forename", $content);
    }
}

class SubPresenterTest extends RhubarbTestCase
{
    /**
     * @var Hosted
     */
    public static $hosted;

    /**
     * @var UnitTestView
     */
    public static $hostedView;

    protected function setUp()
    {
        parent::setUp();

        self::$hosted = new Hosted("Goats");
        self::$hostedView = new UnitTestView();
    }

    public function testAutoBindingGet()
    {
        $host = new Host();
        $host->initialise();

        $this->assertEquals(3, (int)self::$hosted->model->NumberOfGoats);
    }

    public function testAutoBindingSet()
    {
        $host = new Host();
        $host->initialise();
        $host->processUserInterfaceEvents();

        self::$hosted->simulateChangeOfGoats();

        $this->assertEquals(4, (int)self::$hosted->model->NumberOfGoats);
    }

    public function testSubPresenterGetsPath()
    {
        $host = new Host();
        $host->initialise();

        $this->assertEquals("_1Goats", self::$hosted->getPresenterPath());
    }

    public function testClientSideEventsAreRecognised()
    {
        $_REQUEST["_mvpEventName"] = "MvpTestEventReceived";
        $_REQUEST["_mvpEventTarget"] = "_1Goats";

        $host = new Host();
        $host->generateResponse();

        $this->assertEquals("received", $host->model->MvpEvent);
    }

    public function testClientSideEventsTargetPresenter()
    {
        $_REQUEST["_mvpEventName"] = "TestEvent";
        $_REQUEST["_mvpEventTarget"] = "";

        $response = false;

        $host = new Host();
        $host->attachEventHandler("TestEvent", function () use (&$response) {
            $response = true;
        });

        $host->generateResponse(new WebRequest());

        $this->assertTrue($response);
    }

    public function testClientSideEventDoesntGenerateHtml()
    {
        // Check that a client side event does not output HTML when not required.
        $_REQUEST["_mvpEventName"] = "UnmatchedEvent";
        $_REQUEST["_mvpEventTarget"] = "_1Goats";
        $_SERVER["HTTP_X_REQUESTED_WITH"] = "xmlhttprequest";

        $host = new Host();
        $response = $host->generateResponse();

        $this->assertEquals("<?xml version=\"1.0\"?>\r\n", $response);
    }

    public function testClientSideEventCanRePresent()
    {
        // Check that a client side event does not output HTML when not required.
        $_REQUEST["_mvpEventName"] = "RePresentEvent";
        $_REQUEST["_mvpEventTarget"] = "";
        $_SERVER["HTTP_X_REQUESTED_WITH"] = "xmlhttprequest";

        $host = new Host();
        $response = $host->generateResponse();

        $this->assertEquals("<?xml version=\"1.0\"?>
<htmlupdate id=\"\">
Some output
</htmlupdate>", $response);
    }

    public function testClientSideEventCanReturnJson()
    {
        // Check that a client side event does not output HTML when not required.
        $_REQUEST["_mvpEventName"] = "EventWithDataResponse";
        $_REQUEST["_mvpEventTarget"] = "_1Goats";
        $_SERVER["HTTP_X_REQUESTED_WITH"] = "xmlhttprequest";

        $host = new Host();
        $response = $host->generateResponse();

        $this->assertEquals("<?xml version=\"1.0\"?>
<eventresponse event=\"EventWithDataResponse\" sender=\"_1Goats\">
{\"Some\":\"Data\"}
</eventresponse>", $response);
    }
}

class Host extends Presenter
{
    use ModelProvider;

    protected function initialiseModel()
    {
        $this->model->Goats = 3;

        parent::initialiseModel();
    }

    protected function configureView()
    {
        parent::configureView();

        // Attach an event handler to watch for the client side mvp event test.
        $this->view->attachEventHandler("MvpTestEventReceived", function () {
            $this->model->MvpEvent = "received";
        });

        $this->attachEventHandler("RePresentEvent", function () {
            $this->rePresent();
        });
    }

    protected function createView()
    {
        $this->registerView(new AutoBindingViewTest());
    }
}

class Hosted extends ControlPresenter
{
    protected function getPublicModelPropertyList()
    {
        $properties = parent::getPublicModelPropertyList();
        $properties[] = "NumberOfGoats";

        return $properties;
    }

    protected function applyBoundData($data)
    {
        $this->model->NumberOfGoats = $data;
    }

    protected function extractBoundData()
    {
        return $this->model->NumberOfGoats;
    }

    public function simulateChangeOfGoats()
    {
        $this->model->NumberOfGoats = 4;
        $this->setBoundData();
    }

    protected function configureView()
    {
        parent::configureView();

        $this->attachEventHandler("EventWithDataResponse", function () {
            $data = new \stdClass();
            $data->Some = "Data";

            return $data;
        });
    }

    protected function createView()
    {
        $this->registerView(SubPresenterTest::$hostedView);
    }
}

class AutoBindingViewTest extends UnitTestView
{
    public function createPresenters()
    {
        $presenter = SubPresenterTest::$hosted;

        // Attach an event handler to watch for the client side mvp event test.
        $presenter->attachEventHandler("MvpTestEventReceived", function () {
            $this->raiseEvent("MvpTestEventReceived");
        });

        $this->addPresenters($presenter);

        parent::createPresenters();
    }

    public function printViewContent()
    {
        print "Some output";
    }
}
