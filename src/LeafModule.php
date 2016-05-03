<?php

namespace Rhubarb\Leaf;

use Rhubarb\Crown\Module;
use Rhubarb\Leaf\CustardCommands\CreateLeafCommand;

class LeafModule extends Module
{
    public function getCustardCommands()
    {
        return [
            new CreateLeafCommand()
        ];
    }
}