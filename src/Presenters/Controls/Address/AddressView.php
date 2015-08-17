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
            new TextBox("HouseNumber", 20),
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
        if (isset( $this->defaultValues[ "Country" ] )) {
            $country->setSelectedItems( $this->defaultValues[ "Country" ] );
        }
    }

    public function printViewContent()
    {
        $this->printFieldset( "", [ "Country" ] );
        ?>
        <div class="search-fields">
            <span class="search-results"></span>
            <?php
                $this->printFieldset( "", [
                    "HouseNumber",
                    "Post Code" => "PostCodeSearch",
                    "Search"
                ] );
            ?>
            <span class="search-error">Insert a valid Post Code</span>
        </div>

        <br/>
        <p class="manual-address-par">Don't know the postcode? <a class="manual-address-link" href='#'>enter their address manually</a>.</p>
        <br/>
        <a class="search-address-link" href='#'>Search again</a>
        <div class="manual-fields">
            <?php $this->printFieldset( "", [ "Line1", "Line2", "Town", "County", "PostCode" ] ); ?>
        </div>
        <?php
    }
}