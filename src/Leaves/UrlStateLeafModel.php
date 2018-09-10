<?php

namespace Rhubarb\Leaf\Leaves;

class UrlStateLeafModel extends LeafModel
{
    /**
     * @var string The name of the GET param which will provide state for this leaf in the URL
     */
    public $urlStateName;

        protected function getExposableModelProperties()
        {
            $properties = parent::getExposableModelProperties();
            $properties[] = 'urlStateName';
            return $properties;
        }
}
