<?php


namespace Rhubarb\Leaf\Presenters\Controls\Address;


use Rhubarb\Leaf\Presenters\Controls\ControlPresenter;

class AddressUkPafLookup extends ControlPresenter
{
    private $defaultValues;
    protected $view;

    public function __construct($name = "")
    {
        parent::__construct($name);
    }

    protected function createView()
    {
        $view = new AddressUkPafLookupView();
        return $view;
    }

    protected function configureView()
    {
        parent::configureView();
        $this->view->defaultValues = $this->defaultValues;

        $this->view->AttachEventHandler( "SearchPressed", function ( $houseNumber, $postCodeSearch ) {
            if(!isset($postCodeSearch)) {
                return json_decode([]);
            }
            PafSettings::setPostCode($postCodeSearch);
            if(isset($houseNumber)) {
                PafSettings::setHouseNumber($houseNumber);
            }
            $requestUrl = PafSettings::getUrlRequest();
            $response = file_get_contents($requestUrl);
            return json_decode($response);
        } );
    }
}