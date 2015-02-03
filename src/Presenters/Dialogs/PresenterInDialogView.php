<?php

namespace Rhubarb\Leaf\Presenters\Dialogs;

use Rhubarb\Leaf\Presenters\Presenter;

class PresenterInDialogView extends DialogView
{
    private $_presenterToDisplay;
    private $_title;

    public function __construct( $title, Presenter $presenterToDisplay )
    {
        $this->_presenterToDisplay = $presenterToDisplay;
        $this->_title = $title;
    }

    protected function GetTitle()
    {
        return $this->_title;
    }

    public function createPresenters()
    {
        parent::createPresenters();

        $this->addPresenters( $this->_presenterToDisplay );
    }

    protected function PrintDialogContent()
    {
        print $this->_presenterToDisplay;
    }
}