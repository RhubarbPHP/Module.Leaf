<?php

namespace Rhubarb\Leaf\UnitTesting\Presenters\Cruds2;

use Rhubarb\Crown\Patterns\Mvp\Crud\ModelForm\ModelFormPresenter;

class Cruds2EditPresenter extends ModelFormPresenter
{
	protected function CreateView()
	{
		return new Cruds2EditView();
	}

}