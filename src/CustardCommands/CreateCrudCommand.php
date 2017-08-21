<?php

namespace Rhubarb\Leaf\Crud\Custard;

use Rhubarb\Custard\Command\CustardCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCrudLeafCommand extends CustardCommand
{
	protected function configure()
	{
		parent::configure();
		$this->setName("leaf:create-crud-leaf");
	}

	private function getNamespaceFromPath()
	{
		$map = include(VENDOR_DIR . "/composer/autoload_psr4.php");
		$path = getcwd();

		foreach ($map as $stubNamespace => $stubPaths) {
			foreach ($stubPaths as $stubPath) {
				if (stripos($path, $stubPath) === 0) {
					// Found the right stub.
					$folders = str_replace($stubPath, "", $path);
					$namespace = rtrim($stubNamespace . trim(str_replace("/", '\\', $folders), "\\"), "\\");

					return $namespace;
				}
			}
		}

		return false;
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$name = $this->askQuestion("Enter the name of the Model you want to CRUD", "", true);

		$collectionName = $name . "Collection";
		$itemName = $name . "Item";

		$namespace = $this->getNamespaceFromPath();
		$namespaceStatement = "";

		if ($namespace) {
			$namespaceStatement = "
namespace {$namespace};
";
		}
// Collection Classes
		file_put_contents($collectionName . ".php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Crud\Leaves\CrudLeaf;
use Rhubarb\Leaf\Crud\Leaves\CrudModel

class {$collectionName} extends CrudLeaf
{
    /**
    * @var CrudModel
    */
    protected \$model;
    
    protected function getViewClass()
    {
        return {$collectionName}View::class;
    }
}
END
		);
		file_put_contents($collectionName . "View.php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Crud\Leaves\CrudView;
use Rhubarb\Leaf\Crud\Leaves\CrudModel

class {$collectionName}View extends CrudView
{
    /**
    * @var CrudModel
    */
    protected \$model;
    
    protected function printViewContent()
    {
        // Print your HTML here.
    }
}
END
		);

		// Item Classes
		file_put_contents($itemName . ".php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Crud\Leaves\CrudLeaf;
use Rhubarb\Leaf\Crud\Leaves\CrudModel

class {$itemName} extends CrudLeaf
{
    /**
    * @var CrudModel
    */
    protected \$model;
    
    protected function getViewClass()
    {
        return {$itemName}View::class;
    }
}
END
		);
		file_put_contents($itemName . "View.php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Crud\Leaves\CrudView;
use Rhubarb\Leaf\Crud\Leaves\CrudModel

class {$itemName}View extends CrudView
{
    /**
    * @var CrudModel
    */
    protected \$model;
    
    protected function createSubLeaves()
    {
        // The parent creates a Save, Cancel and Delete Leaf for you
        parent::createSubLeaves();
    }
    
    protected function printViewContent()
    {
        // Print your HTML here.
    }
}
END
		);
	}
}
