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

namespace Rhubarb\Leaf\Exceptions;

use Rhubarb\Crown\Exceptions\RhubarbException;

/**
 * A very specialised exception to cause the regeneration of a presenter's view
 *
 * This has to be thrown during the event processing and allows a sub presenter to
 * ensure the presenter resets and reconfigures the view.
 *
 * For example SwitchedPresenter uses this when it knows that the switched presenter needs
 * to change - it must make sure the rendering pipeline re-initialises the view.
 */
class RequiresViewReconfigurationException extends RhubarbException
{
}