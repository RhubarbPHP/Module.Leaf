<?php

namespace Rhubarb\Leaf\Tests\Fixtures;

use Rhubarb\Leaf\Leaves\LeafModel;

class TestLeafModel extends LeafModel
{
    /**
     * @var static
     */
    private static $model = null;

    public function __construct()
    {
        self::$model = $this;
    }

    /**
     * @return static
     */
    public static function getModel()
    {
        return self::$model;
    }
}