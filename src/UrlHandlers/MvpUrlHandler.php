<?php

namespace Rhubarb\Leaf\UrlHandlers;

require_once __DIR__."/../../Core/UrlHandlers/NamespaceMappedUrlHandler.class.php";

/**
 * The URL handler for MVP activity
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 */
use Rhubarb\Crown\UrlHandlers\NamespaceMappedUrlHandler;

class MvpUrlHandler extends NamespaceMappedUrlHandler
{
	protected function ConvertUrlToClassName( $pageUrl )
	{
		return parent::ConvertUrlToClassName( $pageUrl )."Presenter";
	}
}
