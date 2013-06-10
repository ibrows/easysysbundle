<?php
namespace Ibrows\EasySysBundle\Command;
use Symfony\Component\Console\Input\InputOption;

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

class ImportCommand extends ContainerAwareCommand
{
    const ACTION_ALL = 'all';
    protected $steps = 200;

    public static function getActions()
    {
        return IbrowsEasySysBundle::getTypes();
    }

    protected function configure()
    {

        $this->setName('ibrows:easysys:import')->setDescription('Import from Easysys');
        $this->addArgument('toimport', InputArgument::OPTIONAL, implode(' | ', array_keys(self::getActions())), self::ACTION_ALL);
        $this->addOption('onlylist','l', InputOption::VALUE_NONE);
        $this->addOption('force','f', InputOption::VALUE_NONE);
        $this->addOption('stepsize','t', InputOption::VALUE_OPTIONAL,'how big one api call is', $this->steps);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->steps = $input->getOption('stepsize');
        $action = $input->getArgument('toimport');
        $force = $input->getOption('force');
        $actions = self::getActions();
        if ($action == self::ACTION_ALL) {
            foreach(self::getActions() as $action){
                $this->import($actions[$action],$output,$input->getOption('onlylist'),$force);
            }
        } else if (array_key_exists($action, $actions)) {
            if ($this->import($actions[$action],$output,$input->getOption('onlylist'),$force)) {
                exit(0);
            } else {
                exit(1);
            }
        } else {
            throw new \Exception("$action not valid use 'all' or: " . implode(' | ', self::getActions()));
        }

    }

    protected function import($type, $output,$onlylist = false, $force =false)
    {
        $output->writeln("start import <info>{$type}s</info>");
        $connection = $this->getContainer()->get('ibrows.easysys.connection');
        $connection->setOutput($output);
        $handlerid = $this->getContainer()->getParameter('ibrows_easy_sys.handlerservice');
        $handler = $this->getContainer()->get($handlerid);

        /* @var $handler HandlerInterface   */
        $handler->setOutput($output);

        try {
            $countcalls = 0;
            do{
                $output->writeln("get <info>{$type}s</info> " . $this->steps*$countcalls . " - " . ($this->steps*$countcalls + $this->steps) );
                $data = $connection->call($type, array(), array(), "GET",$this->steps,$this->steps*$countcalls);
                $count = sizeof($data);
                if($onlylist){
                    var_dump($data);
                }else{
                    $saved = $handler->saveData($data, $type);
                    if ($saved) {
                        $output->writeln("saved {$count} <info>{$type}s</info>");
                    } else {
                        $output->writeln("dont can't save <error>{$type}s</error>");
                        return false;
                    }
                }
                $countcalls++;
            }while($count == $this->steps);
        }catch (ConnectionException $e) {
             throw $e;
        }catch (\Exception $e){
            $output->writeln("can't save data for type <info>{$type}s</info>");
            $output->writeln(print_r($data,true));
            $saved = false;
            if(!$force)
                throw $e;
            else
                $output->writeln('<error>'.$e->getMessage() .'||' .$e->getTraceAsString().'</error>');
        }
        if(!$onlylist && $saved){
            try {
               $handler->complete();
            }catch (\Exception $e){
                if(!$force)
                    throw $e;
                else
                    $output->writeln('<error>'.$e->getMessage() .'||' .$e->getTraceAsString().'</error>');
            }
        }

        return true;


    }
}
