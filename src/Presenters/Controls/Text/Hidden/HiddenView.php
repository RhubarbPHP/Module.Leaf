<?php

namespace Rhubarb\Leaf\Presenters\Controls\Text\Hidden;


use Rhubarb\Leaf\Presenters\Controls\ControlView;

class HiddenView extends ControlView
{
    protected function printViewContent()
    {
        ?>
        <input type="hidden" <?= $this->getNameValueClassAndAttributeString(); ?>/>
        <?php
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . '/HiddenViewBridge.js';

        return $package;
    }

    protected function getClientSideViewBridgeName()
    {
        return "HiddenViewBridge";
    }
}
