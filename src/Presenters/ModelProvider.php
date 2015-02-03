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

/**
 * Replaces the default bubbling up behaviour of the GetBoundData and SetBoundData events by
 * actually handling the binding calls.
 *
 * This trait should be applied to the host presenter to make sure sub presenters are able to bind to it.
 *
 */
trait ModelProvider
{
    protected $modelProvider = true;

    /**
     * Updates the model with data bound to a sub presenter.
     *
     * @param string $dataKey
     * @param $data
     * @param bool $viewIndex
     */
    protected function setDataFromPresenter($dataKey, $data, $viewIndex = false)
    {
        $this->setData($dataKey, $data, $viewIndex);

        $this->onModelUpdatedFromSubPresenter();
    }

    protected function setData($dataKey, $data, $viewIndex = false)
    {
        if ($viewIndex !== false && $viewIndex !== "") {
            if (!isset($this->model[$dataKey])) {
                $this->model[$dataKey] = [];
            }

            $modelData = $this->model[$dataKey];

            if (!is_array($modelData)) {
                $modelData = [$modelData];
            }

            $modelData[$viewIndex] = $data;

            $this->model[$dataKey] = $modelData;
        } else {
            $this->model[$dataKey] = $data;
        }
    }

    protected function onModelUpdatedFromSubPresenter()
    {

    }

    /**
     * Provides model data to the requesting presenter.
     *
     * @param string $dataKey
     * @param bool $viewIndex
     * @return null
     */
    protected function getDataForPresenter($dataKey, $viewIndex = false)
    {
        return $this->getData($dataKey, $viewIndex);
    }

    protected function getData($dataKey, $viewIndex = false)
    {
        if (!isset($this->model[$dataKey])) {
            return $this->raiseEvent("GetData", $dataKey, $viewIndex);
        }

        if ($viewIndex !== "" && $viewIndex !== false) {
            if (isset($this->model[$dataKey][$viewIndex])) {
                return $this->model[$dataKey][$viewIndex];
            }
        } else {
            return $this->model[$dataKey];
        }

        return null;
    }

    protected function getModel()
    {
        return $this->model;
    }
}
