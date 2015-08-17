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
            $url = "http://paf.gcdtech.com/paf-data.php?simple=1&api=2&output=json&apikey=GCDTEST123";
            if(!isset($postCodeSearch)) {
                return json_decode([]);
            }
            $url .= "&postcode=" . urlencode($postCodeSearch);
            if(isset($houseNumber)) {
                $url .=  "&num=" . $houseNumber;
            }
            $response = file_get_contents($url);
            return json_decode($response);
        } );
    }
}