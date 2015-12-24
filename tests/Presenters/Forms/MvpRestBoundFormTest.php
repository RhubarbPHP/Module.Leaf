<?php

namespace Rhubarb\Leaf\Tests\Presenters\Forms;

use Rhubarb\Crown\Request\WebRequest;
use Rhubarb\Crown\Tests\RhubarbTestCase;
use Rhubarb\Leaf\Presenters\Controls\CheckBoxes\CheckBox;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;
use Rhubarb\Leaf\Presenters\Controls\Text\Password\Password;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;
use Rhubarb\Leaf\Presenters\Forms\MvpRestBoundForm;
use Rhubarb\Leaf\UrlHandlers\MvpRestHandler;
use Rhubarb\Leaf\Views\HtmlView;
use Rhubarb\Stem\Collections\Collection;
use Rhubarb\Stem\Repositories\MySql\Schema\Columns\MySqlEnumColumn;
use Rhubarb\Stem\Tests\Fixtures\User;

class MvpRestBoundFormTest extends RhubarbTestCase
{
    public function testPresenterGetsRestModel()
    {
        $user = new User();
        $user->Username = "windychicken";
        $user->Save();

        $restHandler = new MvpRestHandler(User::class, "", ModelBoundTestForm::class);
        $restHandler->SetUrl("/users/");

        $request = new WebRequest();
        $request->UrlPath = "/users/" . $user->UserID . "/";
        $request->Server("HTTP_ACCEPT", "text/html");
        $request->Server("REQUEST_METHOD", "get");

        $response = $restHandler->generateResponse($request);

        $mvp = $response->GetGenerator();
        $restModel = $mvp->GetRestModel();
        $restCollection = $mvp->GetRestCollection();

        $this->assertEquals($user->Username, $restModel->Username);

        // Make sure the presenter model is left alone
        $this->assertEquals(3, $mvp->model->NewValue);
        $this->assertNull($restModel->NewValue);

        $mvp->PublicSetDataFromPresenter("Test", "TestValue");

        $this->assertEquals("TestValue", $mvp->model->Test);
        $this->assertEquals("TestValue", $restModel->Test);
    }

    public function testControlCreation()
    {
        $mvp = new ModelBoundTestForm();
        $mvp->setRestModel(new User());

        $control = $mvp->PublicCreatePresenterByName("NonExistant");

        $this->assertNull($control);

        $control = $mvp->PublicCreatePresenterByName("Username");

        $this->assertInstanceOf(TextBox::class, $control);

        $control = $mvp->PublicCreatePresenterByName("Password");

        $this->assertInstanceOf(Password::class, $control);

        $control = $mvp->PublicCreatePresenterByName("Active");

        $this->assertInstanceOf(CheckBox::class, $control);

        $control = $mvp->PublicCreatePresenterByName("UserType");

        $this->assertInstanceOf(DropDown::class, $control);

        $items = $control->getSelectionItems();

        $this->assertEquals(["", "Please Select"], $items[0]);
        $this->assertInstanceOf(MySqlEnumColumn::class, $items[1]);

        $control = $mvp->PublicCreatePresenterByName("CompanyID");

        $this->assertInstanceOf(DropDown::class, $control);

        $items = $control->getSelectionItems();

        $this->assertEquals(["", "Please Select"], $items[0]);
        $this->assertInstanceOf(Collection::class, $items[1]);
    }
}

class ModelBoundTestForm extends MvpRestBoundForm
{
    public function PublicSetDataFromPresenter($dataKey, $value)
    {
        $this->setDataFromPresenter($dataKey, $value);
    }

    public function PublicCreatePresenterByName($presenterName)
    {
        return $this->createPresenterByName($presenterName);
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