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

namespace Rhubarb\Leaf\Leaves;

use Rhubarb\Crown\Deployment\ResourceDeploymentPackage;

class LeafDeploymentPackage extends ResourceDeploymentPackage
{
    /**
     * @param string[] ...$localFileToDeploy Path to a local file to deploy.
     */
    public function __construct(...$localFileToDeploy)
    {
        $this->resourcesToDeploy[] = VENDOR_DIR."/rhubarbphp/module-jsvalidation/src/validation.js";
        $this->resourcesToDeploy[] = __DIR__."/../Views/ViewBridge.js";
        $this->resourcesToDeploy = array_merge($this->resourcesToDeploy, $localFileToDeploy);
    }
}