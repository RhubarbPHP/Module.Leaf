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

        $this->addArgument("name", InputOption::VALUE_OPTIONAL, "The name of the leaf class to create.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument("name");

        if (!$name) {
            $name = $this->askQuestion("Enter the name for the Leaf class", "", true);
        }

        file_put_contents($name.".php", <<<END
<?php

class {$name} extends Leaf
{
}
END
);
        
    }
}