<?php

namespace Rhubarb\Leaf\UrlHandlers;

use Rhubarb\Crown\Request\Request;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\UrlHandlers\CollectionUrlHandling;
use Rhubarb\Crown\UrlHandlers\UrlHandler;

class MvpCollectionUrlHandler extends UrlHandler
{
	use CollectionUrlHandling;

	protected $_collectionPresenterClassName;
	protected $_itemPresenterClassName;
	protected $_additionalPresenterClassNameMap = [];
	protected $_urlAction = "";

	/**
	 * @param string $collectionPresenterClassName The full namespaced class name of the presenter representing the collection
	 * @param string $itemPresenterClassName The full namespaced class name of the presenter representing an individual item
	 * @param array $additionalPresenterClassNameMap An optional associative array mapping 'actions' to other presenters.
	 * @param array $children
	 */
	public function __construct(
		$collectionPresenterClassName,
		$itemPresenterClassName,
		$additionalPresenterClassNameMap = [],
		$children = [] )
	{
		parent::__construct( $children );

		$this->_collectionPresenterClassName = $collectionPresenterClassName;
		$this->_itemPresenterClassName = $itemPresenterClassName;
		$this->_additionalPresenterClassNameMap = $additionalPresenterClassNameMap;
	}

	/**
	 * Should be implemented to return a true or false as to whether this handler supports the given request.
	 *
	 * Normally this involves testing the request URI.
	 *
	 * @param Request $request
	 * @param string $currentUrlFragment
	 * @return bool
	 */
	protected function GetMatchingUrlFragment( Request $request, $currentUrlFragment = "" )
	{
		$uri = $currentUrlFragment;

		$parentResponse = parent::GetMatchingUrlFragment( $request, $currentUrlFragment );

		if ( preg_match( "|^".$this->_url."([^/]+)/|", $uri, $match ) )
		{
			if ( isset( $this->_additionalPresenterClassNameMap[ $match[1] ] ) )
			{
				$this->_urlAction = $match[1];
			}
			else
			{
				$this->_resourceIdentifier = $match[1];
				$this->_isCollection = false;
			}

			return $match[0];
		}

		return $parentResponse;
	}

	protected function GetPresenterClassName()
	{
		$mvpClass = false;

		if ( $this->_urlAction != "" )
		{
			if ( isset( $this->_additionalPresenterClassNameMap[ $this->_urlAction ] ) )
			{
				$mvpClass = $this->_additionalPresenterClassNameMap[ $this->_urlAction ];
			}
		}

		if ( $mvpClass === false )
		{
			if ( $this->IsCollection() )
			{
				$mvpClass = $this->_collectionPresenterClassName;
			}
			else
			{
				$mvpClass = $this->_itemPresenterClassName;
			}
		}

		return $mvpClass;
	}

	/**
	 * Return the response if appropriate or false if no response could be generated.
	 *
	 * @param mixed $request
	 * @return bool
	 */
	protected function GenerateResponseForRequest( $request = null )
	{
		$mvpClass = $this->GetPresenterClassName();
		$mvp = new $mvpClass();
		$mvp->ItemIdentifier = $this->_resourceIdentifier;

		$response = $mvp->GenerateResponse( $request );

		if ( is_string( $response ) )
		{
			$htmlResponse = new HtmlResponse( $mvp );
			$htmlResponse->SetContent( $response );
			$response = $htmlResponse;
		}

		return $response;
	}
}