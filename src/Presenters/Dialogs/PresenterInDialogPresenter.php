<?php

namespace Rhubarb\Leaf\Presenters\Dialogs;

use Rhubarb\Leaf\Presenters\HtmlPresenter;
use Rhubarb\Leaf\Presenters\Presenter;

class PresenterInDialogPresenter extends DialogPresenter
{
    private $_presenterToDisplay;
    private $_dialogTitle;

    public function __construct( $name, $dialogTitle, Presenter $presenterToDisplay )
    {
        parent::__construct( $name );

        $this->_dialogTitle = $dialogTitle;
        $this->_presenterToDisplay = $presenterToDisplay;
    }

    protected function createView()
    {
        return new PresenterInDialogView( $this->_dialogTitle, $this->_presenterToDisplay );
    }
} 