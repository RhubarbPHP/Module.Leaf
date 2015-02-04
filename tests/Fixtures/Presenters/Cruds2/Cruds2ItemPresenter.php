<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Cruds2;

use Rhubarb\Leaf\Views\HtmlView;
use Rhubarb\Patterns\Mvp\Crud\ModelForm\ModelFormPresenter;

class Cruds2ItemPresenter extends ModelFormPresenter
{
	protected function CreateView()
	{
		return new HtmlView();
	}
}