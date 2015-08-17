<?php


namespace Rhubarb\Leaf\Presenters\Controls\Address;


use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class Address extends ControlPresenter
{
    private $defaultValues;
    protected $view;

    public function __construct($name = "", $defaultValues = [])
    {
        parent::__construct($name);
        $this->defaultValues = $defaultValues;
    }

    protected function createView()
    {
        $view = new AddressView();
        return $view;
    }

    protected function configureView()
    {
        parent::configureView();
        $this->view->defaultValues = $this->defaultValues;
    }

}