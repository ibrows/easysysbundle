<?php

namespace Ibrows\EasySysBundle\SaveHandler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author marcsteiner
 * @author dominikzogg
 */
class BaseHandler implements HandlerInterface
{
    /**
     * @var OutputInterface
     */
    protected $out;

    /**
     * @var EntityManager
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
     * @var string
     */
    protected $prefix = 'Easysys';

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->em = $doctrine->getEntityManager();
        $this->out = new NullOutput();
        $this->classmap = array();
    }

    /**
     * @param array $data
     * @param null $type
     * @return bool
     */
    public function saveData(array $data, $type = null)
    {
        $class = $this->getClassForType($type);
        if($class == null){
            return false;
        }
        $object = new $class();
        foreach($data as $entry) {
            foreach ($entry as $field => $value) {
                $methodName = 'set' . $this->prefix . ucfirst($field);
                if (method_exists($object, $methodName)) {
                    $object->$methodName($value);
                }
            }
        }

        $this->em->persist($object);
        return true;
    }

    public function complete()
    {
        $this->em->flush();
    }

    /**
     * @param OutputInterface $out
     */
    public function setOutput(OutputInterface $out)
    {
        $this->out = $out;
    }

    /**
     * @param string $type
     * @return string
     */
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
     * @return $this
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
     * @return $this
     */
    public function setDefaultClass($defaultClass)
    {
        $this->defaultClass = $defaultClass;
        return $this;
    }
}
