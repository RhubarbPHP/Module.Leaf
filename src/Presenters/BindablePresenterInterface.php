<?php

namespace Rhubarb\Leaf\Presenters;

interface BindablePresenterInterface
{
    public function getBindingValue();
    public function setBindingValue($bindingValue);
}