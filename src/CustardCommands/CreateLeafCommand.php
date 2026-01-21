<?php

namespace Rhubarb\Leaf\CustardCommands;

use Rhubarb\Custard\Command\CustardCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLeafCommand extends CustardCommand
{
	protected function configure()
	{
		parent::configure();

		$this->setName("leaf:create-leaf")
			->addArgument("name", InputOption::VALUE_OPTIONAL, "The name of the leaf class to create.")
			->addOption('viewbridge');
	}

	protected function getNamespaceFromPath()
	{
		$map = include(VENDOR_DIR."/composer/autoload_psr4.php");
		$path = getcwd();

		foreach($map as $stubNamespace => $stubPaths ){
			foreach($stubPaths as $stubPath) {
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
		$name = $input->getArgument("name");
		$generateViewbridge = $input->getOption('viewbridge') != null;

		if (sizeof($name) == 0) {
			$name = $this->askQuestion("Enter the name for the Leaf class", "", true);
		} else {
			$name = $name[0];
		}

		$viewBridgeMethods = $generateViewbridge ? $this->getViewBridgeMethods($name) : "";
		$viewBridgeUseStatement = $generateViewbridge ? "
use Rhubarb\Leaf\Leaves\LeafDeploymentPackage;
" : "";

		$namespace = $this->getNamespaceFromPath();
		$namespaceStatement = "";

		if ($namespace){
			$namespaceStatement = "
namespace {$namespace};
";
		}

		file_put_contents($name.".php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Leaves\Leaf;

class {$name} extends Leaf
{
    /**
    * @var {$name}Model
    */
    protected \$model;
    
    protected function getViewClass()
    {
        return {$name}View::class;
    }
    
    protected function createModel()
    {
        \$model = new {$name}Model();

        // If your model has events you want to listen to you should attach the handlers here
        // e.g.
        // \$model->selectedUserChangedEvent->attachListener(function(){ ... });

        return \$model;
    }
}
END
		);
		file_put_contents($name."View.php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Views\View;
{$viewBridgeUseStatement}
class {$name}View extends View
{
    /**
    * @var {$name}Model
    */
    protected \$model;
    
    protected function printViewContent()
    {
        // Print your HTML here.
    }
{$viewBridgeMethods}
}
END
		);

		file_put_contents($name."Model.php", <<<END
<?php
$namespaceStatement
use Rhubarb\Leaf\Leaves\LeafModel;

class {$name}Model extends LeafModel
{
    // Define public properties for your module
    // e.g.
    //
    // /**
    //  * The selected user
    //  *
    //  * @var string 
    //  */
    // public \$selectedUser;
    //
    // Also you can should define any events you need to raise
    // e.g.
    //
    // /**
    //  * Raised when the selected user changes.
    //  *
    //  * @var Rhubarb\Crown\Events\Event 
    //  */
    // public \$selectedUserChangedEvent;

    public function __construct()
    {
        parent::__construct();
        
        // Here you should initialise any event handlers to a new Event object
        // e.g.
        // \$this->selectedUserChangedEvent = new Event();
        //
        // You can also non scalar properties to initial values.
    }
}
END
		);

		if ($generateViewbridge) {
			file_put_contents(
				$name . 'ViewBridge.js',
				$this->generateViewBridgeContent($name)
			);
		}
	}

	protected function generateViewBridgeContent($name)
	{
		return<<<END
var bridge = function (leafPath) {
    window.rhubarb.viewBridgeClasses.ViewBridge.apply(this, arguments);
};

bridge.prototype = new window.rhubarb.viewBridgeClasses.ViewBridge();

bridge.prototype.attachEvents = function () {
    // TODO: Implement attachEvents
};

bridge.prototype.constructor = bridge;

window.rhubarb.viewBridgeClasses.{$name}ViewBridge = bridge;
END;
	}

	protected function getViewBridgeMethods($name)
	{
		return <<<END

    protected function getViewBridgeName()
    {
        return "{$name}ViewBridge";
    }

    public function getDeploymentPackage()
    {
        return new LeafDeploymentPackage(__DIR__ . "/" . \$this->getViewBridgeName() . ".js");
    }
END;
	}
}
