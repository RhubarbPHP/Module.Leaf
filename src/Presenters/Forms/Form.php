<?php

namespace Rhubarb\Leaf\Presenters\Forms;

require_once __DIR__."/../HtmlPresenter.class.php";

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\ModelProvider;

/**
 * A presenter that emits an HTML form tag around it's view.
 *
 * This basic plumbing allows for HTTP post to initiate commands on the presenter.
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
class Form extends HtmlPresenter
{
	use ModelProvider;

	public function __construct( $name = "" )
	{
		if ( $name == "" )
		{
			$name = basename( str_replace( "\\", "/", get_class( $this ) ) );
		}

		parent::__construct( $name );
	}
}
