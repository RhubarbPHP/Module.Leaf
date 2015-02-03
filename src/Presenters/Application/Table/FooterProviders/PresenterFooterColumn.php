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

namespace Rhubarb\Leaf\Presenters\Application\Table\FooterProviders;

require_once __DIR__ . '/FooterColumn.php';

use Rhubarb\Leaf\Presenters\Application\Table\Table;
use Rhubarb\Leaf\Presenters\Presenter;

/**
 * A column type which asks another presenter to present inside each cell.
 */
class PresenterFooterColumn extends FooterColumn
{
	/**
	 * @var Presenter
	 */
	protected $presenter;

	public function __construct( Presenter $presenter, $label = "" )
	{
		parent::__construct( $label );

		$this->presenter = $presenter;
	}

	public function etPresenter()
	{
		return $this->presenter;
	}

	public function getCellValue( Table $table )
	{
		return (string) $this->presenter;
	}
}