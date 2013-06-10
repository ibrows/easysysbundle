<?php

namespace Ibrows\EasySysBundle\SaveHandler;
use Symfony\Component\Console\Output\NullOutput;

use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

/**
 * @author marcsteiner
 *
 */
class BaseHandler implements HandlerInterface
{

    /**
     * @var OutputInterface
     */
    protected $out;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @var array
     */
    protected $classmap;

    /**
     * @var string
     */
    protected $defaultClass = '\stdClass';

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->em = $doctrine->getEntityManager();
        $this->out = new NullOutput();
        $this->classmap = array();
    }

    public function saveData(array $data, $type = null)
    {
        $class = $this->getClassForType($type);
        if($class == null){
            return false;
        }
        $object = new $class();
        foreach ($data as $field => $value) {
            $methodName = 'set' . ucfirst($value);
            if (method_exists($object, 'set' . $value)) {
                $class->$methodName($value);
            }
        }
        $this->em->persist($object);
        return true;
    }
    public function complete(){
        $this->em->flush();
    }
    public function setOutput(OutputInterface $out)
    {
        $this->out = $out;
    }

    protected function getClassForType($type)
    {
        if (array_key_exists($type, $this->classmap)) {
            return $this->classmap[$type];
        }
        return $this->defaultClass;
    }

    /**
     * @return array:
     */
    public function getClassmap()
    {
        return $this->classmap;
    }

    /**
     * @param array $classmap
     * @return \Ibrows\EasySysBundle\Connection\BaseHandler
     */
    public function setClassmap(array $classmap)
    {
        $this->classmap = $classmap;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultClass()
    {
        return $this->defaultClass;
    }

    /**
     * @param string $defaultClass
     */
    public function setDefaultClass($defaultClass)
    {
        $this->defaultClass = $defaultClass;
        return $this;
    }

}
