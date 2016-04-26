<?php

namespace Rhubarb\Leaf\Tests\Fixtures\Presenters;

use Rhubarb\Crown\Events\Event;
use Rhubarb\Leaf\Presenters\PresenterModel;

class SimpleModel extends PresenterModel
{
    public $firstEvent;

    public $secondEvent;

    public $saveEvent;

    public $modelSetting;

    public $text;

    public $Forename = "John";

    public function __construct()
    {
        $this->firstEvent = new Event();
        $this->secondEvent = new Event();
        $this->saveEvent = new Event();
    }

    protected function getExposableModelProperties()
    {
        $list = parent::getExposableModelProperties();
        $list[] = "modelSetting";

        return $list;
    }
}