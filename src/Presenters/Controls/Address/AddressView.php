<?php


namespace Rhubarb\Leaf\Presenters\Controls\Address;


use Rhubarb\Leaf\Presenters\Controls\Buttons\Button;
use Rhubarb\Leaf\Presenters\Controls\ControlView;
use Rhubarb\Leaf\Presenters\Controls\Selection\DropDown\DropDown;
use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class AddressView extends ControlView
{
    protected $htmlType = "address";
    public $defaultValues;

    public function __construct($htmlType = "address")
    {
        $this->htmlType = $htmlType;

        $this->requiresContainer = true;
        $this->requiresStateInputs = true;
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/AddressViewBridge.js";

        return $package;
    }

    protected function getClientSideViewBridgeName()
    {
        return "AddressViewBridge";
    }

    public function createPresenters()
    {
        $this->AddPresenters(
            $country = new DropDown("Country"),
            new TextBox("HouseNumber", 4),
            new TextBox("PostCodeSearch", 10),
            $search = new Button("Search", "Search", function() {
            }),
            new TextBox("Line1", 50),
            new TextBox("Line2", 30),
            new TextBox("Town", 30),
            new TextBox("County", 20),
            new TextBox("PostCode", 10)
        );

        $country->SetSelectionItems( [ [ "", "Please select..." ], Country::getCountriesList() ] );
        if(isset($this->defaultValues["Country"]))
        {
            $countryValue = $this->defaultValues["Country"];
            $country->setSelectedItems($countryValue);
        }
    }

    public function printViewContent()
    {
        ?>

        <?php
        $this->printFieldset( "", [ "Country" ] );
        ?>
        <div class="search-fields">
            <?php
                $this->printFieldset( "", [
                    "HouseNumber",
                    "Post Code" => "PostCodeSearch",
                    "Search"
                ] );
            ?>
        </div>
        <br/>
        <p class="manual-address-par">Don't know the postcode? <a class="manual-address-link" href='#'>enter their address manually</a>.</p>
        <br/>
        <a class="search-address-link" href='#'>Search again</a>
        <div class="manual-fields">
            <?php $this->printFieldset( "", [ "Line1", "Line2", "Town", "County", "PostCodeSearch" ] ); ?>
        </div>
        <?php
    }
}