<?php

namespace Rhubarb\Leaf\UrlHandlers;

use Rhubarb\Stem\UrlHandlers\ModelCollectionHandler;
use Rhubarb\Crown\Request\Request;
use Rhubarb\Crown\Response\HtmlResponse;
use Rhubarb\Crown\RestApi\Exceptions\RestImplementationException;

/**
 * A rest handler that handles HTML requests by passing control to MVP presenters.
 */
class MvpRestHandler extends ModelCollectionHandler
{
	protected $_collectionPresenterClassName;
	protected $_itemPresenterClassName;
	protected $_additionalPresenterClassNameMap = [];
	protected $_urlAction = "";

	/**
	 * @param string $modelName The full namespaced class name of the model providing data for the requests
	 * @param string $collectionPresenterClassName The full namespaced class name of the presenter representing the model collection
	 * @param string $itemPresenterClassName The full namespaced class name of the presenter representing an individual item
	 * @param array $additionalPresenterClassNameMap An optional associative array mapping 'actions' to other presenters.
	 * @param array $children
	 */
	public function __construct(
		$modelName,
		$collectionPresenterClassName,
		$itemPresenterClassName,
		$additionalPresenterClassNameMap = [],
		$children = [] )
	{
		parent::__construct( $modelName, $children );

		$this->_collectionPresenterClassName = $collectionPresenterClassName;
		$this->_itemPresenterClassName = $itemPresenterClassName;
		$this->_additionalPresenterClassNameMap = $additionalPresenterClassNameMap;
	}

	protected function GetSupportedMimeTypes()
	{
		$mime = parent::GetSupportedMimeTypes();

		$mime[ "application/core" ] = "mvp";

		return $mime;
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
			if ( is_numeric( $match[1] ) || isset( $this->_additionalPresenterClassNameMap[ $match[1] ] ) )
			{
				$this->_urlAction = $match[1];
				$this->_isCollection = false;

				return $match[0];
			}
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
			else if( is_numeric( $this->_urlAction ) )
			{
				$this->_isCollection = false;
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

		if ( $this->IsCollection() )
		{
			if ( method_exists( $mvp, "SetRestCollection" ) )
			{
				try
				{
					call_user_func( array( $mvp, "SetRestCollection" ), $this->GetModelCollection() );
				}
				catch( RestImplementationException $er )
				{

				}
			}
		}
		else
		{
			if ( method_exists( $mvp, "SetRestModel" ) )
			{
				try
				{
					call_user_func( array( $mvp, "SetRestModel" ), $this->GetModelObject() );
				}
				catch( RestImplementationException $er )
				{}
			}
		}

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