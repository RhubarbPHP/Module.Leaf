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

namespace Rhubarb\Leaf\Presenters\Application\Pager;

require_once __DIR__ . '/PagerView.php';

use Rhubarb\Crown\Html\ResourceLoader;

class EventPagerView extends PagerView
{
	protected function getClientSideViewBridgeName()
	{
		return "EventPager";
	}

	protected function getAdditionalResourceUrls()
	{
		return [ ResourceLoader::getJqueryUrl( "1.9.1" ) ];
	}

	public function getDeploymentPackage()
	{
		$package = parent::getDeploymentPackage();
		$package->resourcesToDeploy[] = __DIR__."/../../../../resources/jquery-presenter.js";
		$package->resourcesToDeploy[] = __DIR__."/../../../../resources/application/event-pager.js";

		return $package;
	}
}