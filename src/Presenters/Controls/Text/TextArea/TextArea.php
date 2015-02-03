<?php
/**
 * Created by JetBrains PhpStorm.
 * User: acuthbert
 * Date: 04/03/13
 * Time: 16:33
 * To change this template use File | Settings | File Templates.
 */

namespace Rhubarb\Leaf\Presenters\Controls\Text\TextArea;

require_once __DIR__."/../TextBox/TextBox.class.php";

use Rhubarb\Leaf\Presenters\Controls\Text\TextBox\TextBox;

class TextArea extends TextBox
{
	public $_rows;
	public $_cols;

	public function __construct( $name = "", $rows = 5, $cols = 40 )
	{
		parent::__construct( $name );

		$this->_rows = $rows;
		$this->_cols = $cols;
	}

	public function SetRows( $rows )
	{
		$this->_rows = $rows;

		return $this;
	}

	public function SetCols( $cols )
	{
		$this->_cols = $cols;

		return $this;
	}

	protected function createView()
	{
		return new TextAreaView();
	}

	protected function configureView()
	{
		parent::configureView();

		$this->view->rows = $this->_rows;
		$this->view->cols = $this->_cols;
	}
}