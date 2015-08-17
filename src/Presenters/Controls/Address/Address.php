<?php


namespace Rhubarb\Leaf\Presenters\Controls\Address;


use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class Address extends ControlPresenter
{
    protected function createView()
    {
        return new AddressView();
    }
}