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

namespace Rhubarb\Leaf\Presenters\Controls;

/**
 * Provides a method for Presenters to compile the view bridge spawn settings of themselves and any
 * sub-presenters, recursive to any level of nested sub-presenters that share this trait.
 */
trait SpawnableByViewBridgeWithSubPresenters
{
    public function compileSpawnableSettings()
    {
        $spawnSettings = [];

        foreach ($this->subPresenters as $presenter) {
            $presenterName = $presenter->getName();
            $spawnSettings[$presenterName] = $presenter->getSpawnStructure();

            if (method_exists($presenter, "compileSpawnableSettings")) {
                $spawnSettings[$presenterName]["SubPresenters"] = $presenter->compileSpawnableSettings();
            }
        }

        $this->model->PresenterSpawnSettings = $spawnSettings;

        return $spawnSettings;
    }
}
