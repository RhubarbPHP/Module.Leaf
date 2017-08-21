<?php

namespace Rhubarb\Leaf;

use Rhubarb\Crown\Module;
use Rhubarb\Leaf\Crud\Custard\CreateCrudLeafCommand;
use Rhubarb\Leaf\CustardCommands\CreateLeafCommand;

class LeafModule extends Module
{
    public function getCustardCommands()
    {
        $commands =  [
			new CreateLeafCommand()
        ];

        if(is_dir(VENDOR_DIR . '/rhubarbphp/module-leaf-crud/'))
		{
			$commands[] = new CreateCrudLeafCommand();
		}

        return $commands;
    }
}