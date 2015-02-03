<?php

/*
 *	Copyright 2015 RhubarbPHP
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License.
 */

namespace Rhubarb\Leaf\Presenters\Dialogs;

require_once __DIR__ . "/../../Views/JQueryView.php";

use Rhubarb\Leaf\Views\JQueryView;

abstract class DialogView extends JQueryView
{
    protected function getTitle()
    {
        return "Unnamed Dialog";
    }

    protected function printTitle()
    {
        ?>
        <div class="dialog__title">
            <?= $this->getTitle(); ?>
        </div>
    <?php
    }

    protected abstract function printDialogContent();

    public function printViewContent()
    {
        ?>
        <div class="dialog">
            <?php

            $this->printTitle();

            ?>
            <div class="dialog__content">
                <?php

                $this->printDialogContent();

                ?>
            </div>
        </div>
    <?php
    }

    protected function getClientSideViewBridgeName()
    {
        return "DialogViewBridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/DialogViewBridge.js";
        $package->resourcesToDeploy[] = __DIR__ . "/DialogViewBridge.css";

        return $package;
    }
}