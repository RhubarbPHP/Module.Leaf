<?php

namespace Rhubarb\Leaf\Presenters\Controls\FileUpload;

use Rhubarb\Leaf\Presenters\Controls\ControlView;

class MultipleHtmlFileUploadView extends SimpleHtmlFileUploadView
{
    public function __construct()
    {
        parent::__construct();

        $this->_requiresContainer = true;
        $this->_requiresStateInputs = true;
    }

    protected function getClientSideViewBridgeName()
    {
        return "MultipleHtmlFileUploadViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__."/MultipleHtmlFileUploadViewBridge.js";

        return $package;
    }

    protected function PrintUploadInput()
    {
        $accepts = "";

        if ( sizeof( $this->filters ) > 0 )
        {
            $accepts = " accept=\"".implode(",", $this->filters )."\"";
        }

        ?>
        <input type="file" name="<?=$this->GetIndexedPresenterPath();?>[]" id="<?=$this->GetIndexedPresenterPath();?>" presenter-name="<?=$this->presenterName?>" <?=$accepts;?> multiple="multiple" />
        <?php
    }
} 