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

namespace Rhubarb\Leaf\Presenters;

require_once __DIR__ . '/Presenter.php';

use Rhubarb\Crown\Html\ResourceLoader;
use Rhubarb\Leaf\Views\SpawnableByViewBridgeViewTrait;

/**
 * Exposes support for spawning a representation of the presenter directly with the view bridge.
 */
class SpawnableByViewBridgePresenter extends Presenter
{
    public final function getSpawnStructure()
    {
        $view = $this->view;

        if (method_exists($view, "getSpawnSettings")) {
            $this->applyModelToView();

            $settings = $view->GetSpawnSettings();

            $deploymentPackage = $view->getDeploymentPackage();
            $deploymentPackage->deploy();

            $urls = $deploymentPackage->getDeployedUrls();

            ResourceLoader::loadResource($urls);

            return $settings;
        }

        return [];
    }
}