<?php
namespace Ibrows\EasySysBundle\Command;
use Ibrows\EasySysBundle\API\Contact;

use Ibrows\EasySysBundle\IbrowsEasySysBundle;

use Ibrows\EasySysBundle\Connection\ConnectionException;

use Ibrows\EasySysBundle\Handler\HandlerInterface;

use Ibrows\SonidoBundle\Entity\Product;

use Symfony\Component\Finder\Finder;

use Symfony\Component\Filesystem\Filesystem;

use Symfony\Component\HttpKernel\KernelInterface;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SaveCommand extends ContainerAwareCommand
{

    public static function getTypes()
    {
        return IbrowsEasySysBundle::getTypes();
    }

    protected function configure()
    {
        $this->setName('ibrows:easysys:save')->setDescription('Import from Easysys');
        $this->addArgument('type', InputArgument::REQUIRED, implode(' | ', self::getTypes()));
        $this->addArgument('vars', InputArgument::IS_ARRAY);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $type = $input->getArgument('type');
        $vars = $input->getArgument('vars');
        $types = self::getTypes();

        if (array_key_exists($type, $types)) {
            if ($this->save($type, $vars, $output)) {
                exit(0);
            } else {
                exit(1);
            }
        } else {
            throw new \Exception("$type not valid use 'all' or: " . implode(' | ', self::getTypes()));
        }
    }

    protected function save($type, $vars, $output)
    {
        $output->writeln("start save <info>{$type}</info>");
        $service = $this->getContainer()->get('ibrows.easysys.' . $type);
        $service->setOutput($output);
        $result = call_user_func_array(array($service, 'save'), $vars);
        var_dump($result);
        return true;
    }
}
