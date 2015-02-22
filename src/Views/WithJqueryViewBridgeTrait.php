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

namespace Rhubarb\Leaf\Views;

trait WithJqueryViewBridgeTrait
{
    protected function getClientSideViewBridgeName()
    {
        $className = get_class();
        $className = substr($className, strrpos($className, "\\") + 1);
        return $className . "Bridge";
    }

    public function getDeploymentPackage()
    {
        $package = parent::getDeploymentPackage();
        $package->resourcesToDeploy[] = __DIR__ . "/../../resources/jquery-presenter.js";
        $package->resourcesToDeploy[] = $this->getDeploymentPackageDirectory(
            ) . "/" . $this->getClientSideViewBridgeName() . ".js";

        return $package;
    }

    /**
     * Implement this and return __DIR__ when your ViewBridge.js is in the same folder as your class
     *
     * @returns string Path to your ViewBridge.js file
     */
    abstract public function getDeploymentPackageDirectory();
}
