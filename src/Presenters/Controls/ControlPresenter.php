<?php

namespace Rhubarb\Leaf\Presenters\Controls;

require_once __DIR__."/../HtmlPresenter.class.php";

use Rhubarb\Crown\Context;
use Rhubarb\Leaf\Presenters\SpawnableByViewBridgePresenter;
use Rhubarb\Crown\String\StringTools;

/**
 * Provides a base class for control presenters
 *
 * Adds data binding support to a hosting presenter's model.
 *
 * @author acuthbert
 * @copyright GCD Technologies 2012
 * @property string $CssClassNames The names of the CSS classes to pass to our view.
 */
class ControlPresenter extends SpawnableByViewBridgePresenter
{
	protected $_label = "";

	public function SetLabel( $labelText )
	{
		$this->_label = $labelText;
	}

	public function AddCssClassNames( $classNames = [] )
	{
		$classes = $this->CssClassNames;

		if ( !is_array( $classes ) )
		{
			$classes = [];
		}

		$classes = array_merge( $classes, $classNames );
		$this->CssClassNames = $classes;
	}

	public function AddCssClassName( $className )
	{
		$this->AddCssClassNames( [ $className ] );
	}

	public function AddHtmlAttribute( $attributeName, $attributeValue )
	{
		$attributes = $this->HtmlAttributes;

		if ( !is_array( $attributes ) )
		{
			$attributes = [];
		}

		$attributes[ $attributeName ] = $attributeValue;

		$this->HtmlAttributes = $attributes;
	}

	protected function applyModelToView()
	{
		$this->view->cssClassNames = $this->CssClassNames;
		$this->view->htmlAttributes = $this->HtmlAttributes;

		parent::applyModelToView();
	}

	protected function ApplyBoundData($data)
	{
		$this->model->Value = $data;
	}

	protected function ExtractBoundData()
	{
		return $this->model->Value;
	}

	protected function parseRequestForCommand()
	{
		$request = Context::CurrentRequest();
		$values = $request->Post( $this->getIndexedPresenterPath() );

		if( $values !== null )
		{
			$this->model->Value = $values;
			$this->SetBoundData();
		}
	}

	/**
	 * Returns a label that the hosting view can use in the HTML output.
	 *
	 * @return string
	 */
	public function getLabel()
	{
		if ( $this->_label != "" )
		{
			return $this->_label;
		}

		return StringTools::WordifyStringByUpperCase( $this->getName() );
	}
}
