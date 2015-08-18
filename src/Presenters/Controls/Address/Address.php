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

        $this->view->AttachEventHandler( "SearchPressed", function ( $houseNumber, $postCodeSearch ) {
            if(!isset($postCodeSearch)) {
                return json_decode([]);
            }
            $requestParams = [ "postcode" => $postCodeSearch ];
            if(isset($houseNumber)) {
                $requestParams["num"] = $houseNumber;
            }
            $requestUrl = PafSettings::getUrlRequest($requestParams);
            $response = file_get_contents($requestUrl);
            return json_decode($response);
        } );
    }
}