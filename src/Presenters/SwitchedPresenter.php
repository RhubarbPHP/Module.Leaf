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

require_once __DIR__ . "/Presenter.php";

use Rhubarb\Leaf\Exceptions\InvalidPresenterNameException;
use Rhubarb\Leaf\Exceptions\RequiresViewReconfigurationException;
use Rhubarb\Leaf\Presenters\Forms\Form;


/**
 * The switched presenter hosts a number of sub presenters and manages selection of the appropriate presenter.
 */
class SwitchedPresenter extends Form
{
    /**
     * A collection of presenter names and class names to use.
     * @var array
     */
    private $switchedPresenters = array();

    /**
     * Override this to return a mapping of presenter names to classes.
     *
     * @see SwitchedPresenter::switchedPresenters;
     */
    protected function getSwitchedPresenters()
    {
        return [];
    }

    protected function changePresenter($newPresenterName)
    {
        if (!isset($this->switchedPresenters[$newPresenterName])) {
            throw new InvalidPresenterNameException($newPresenterName);
        }

        $this->model->CurrentPresenterName = $newPresenterName;

        // We throw this exception to signal that the processing pipeline should reinitialise
        // the presenter. Of course this needs done as the hosted presenter should now be a
        // different one.

        throw new RequiresViewReconfigurationException();
    }

    protected function getPublicModelPropertyList()
    {
        $list = parent::getPublicModelPropertyList();
        $list[] = "CurrentPresenterName";

        return $list;
    }


    protected function createView()
    {
        $this->switchedPresenters = $this->getSwitchedPresenters();

        $this->registerView(new SwitchedPresenterView($this->switchedPresenters));
    }

    protected function configureView()
    {
        $this->view->attachEventHandler(
            "GetCurrentPresenter",
            function () {
                $class = $this->switchedPresenters[$this->getCurrentPresenterName()];

                if (is_string($class)) {
                    $object = new $class();
                } else {
                    $object = $class;
                }

                return $object;
            }
        );

        parent::configureView();
    }

    /**
     * Gets the currently active presenter name.
     */
    public function getCurrentPresenterName()
    {
        if (!isset($this->model->CurrentPresenterName)) {
            return $this->getDefaultPresenterName();
        }

        return $this->model->CurrentPresenterName;
    }

    /**
     * Returns the name of the default presenter name.
     *
     * The default implementation simply returns the first from the collection.
     *
     * @return string
     */
    protected function getDefaultPresenterName()
    {
        reset($this->switchedPresenters);

        return key($this->switchedPresenters);
    }

    protected function onPresenterAdded(Presenter $presenter)
    {
        // Registers with the view's sub presenter to make sure that we get notified
        // when the presenter should change.
        $presenter->attachEventHandler(
            "ChangePresenter",
            function ($presenterName) {
                $this->changePresenter($presenterName);
            }
        );
    }
}