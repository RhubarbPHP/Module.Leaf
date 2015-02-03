<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Cruds;

use Rhubarb\Leaf\Views\HtmlView;
use Rhubarb\Crown\Patterns\Mvp\Crud\ModelForm\ModelFormPresenter;

class CrudsAddPresenter extends ModelFormPresenter
{
	protected function CreateView()
	{
		return new HtmlView();
	}
}