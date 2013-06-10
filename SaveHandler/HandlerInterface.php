<?php

namespace Ibrows\EasySysBundle\SaveHandler;

use Symfony\Component\Console\Output\OutputInterface;

use Doctrine\ORM\EntityManager;

/**
 * @author marcsteiner
 *
 */
interface HandlerInterface
{

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output);

    /**
     * @param array $data
     * @param string $type
     */
    public function saveData(array $data, $type);

    /**
     * Completes a type save action
     */
    public function complete();
}