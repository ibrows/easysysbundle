<?php
namespace Ibrows\EasySysBundle\API;
use Ibrows\EasySysBundle\Connection\ConnectionInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Console\Output\NullOutput;

use Ibrows\EasySysBundle\Connection\Connection;

use Ibrows\EasySysBundle\Connection\ConnectionException;

/**
 * @author marcsteiner
 *
 */
class AbstractType
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Connection
     */
    protected $connection;

    protected $type;
    protected $parentType;
    protected $parentId;

    public function __construct(ConnectionInterface $connection)
    {
        $this->connection = $connection;
        $this->output = new NullOutput();

    }

    /**
     * @param OutputInterface $out
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        $this->connection->setOutput($output);
        return $this;
    }

    /**
     * @param Connection $connection
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
        return $this;
    }

    public function search($crits, $type = null)
    {
        if (!$type) {
            $type = $this->type;
        }
        $call = $type . '/search';
        if ($this->parentType) {
            $call = "{$this->parentType}/{$this->parentId}/$call";
        }
        return $this->connection->call($call, array(), $crits, "POST");
    }

    public function find($simplecrits, $type = null)
    {
        return $this->search($this->convertSimpleCriterias($simplecrits, $type));
    }

    public function findOneByNr($nr)
    {
        $crits = array(
                'nr' => $nr
        );
        $found = $this->find($crits);
        if (sizeof($found) > 0) {
            return $found[0];
        }
        return null;
    }

    public function findOneById($id)
    {
        $call = $this->type . '/' . $id;
        if ($this->parentType) {
            $call = "{$this->parentType}/{$this->parentId}/$call";
        }
        try {
            return $this->connection->call($call, array(), array(), "GET");
        } catch (ConnectionException $e) {
            return null;
        }

    }

    protected function convertSimpleCriterias($simplecrits, $operator = '=')
    {
        $crits = array();
        foreach ($simplecrits as $key => $value) {
            if ($value == null) {
                continue;
            }
            $crits[] = array(
                    'field' => $key,
                    'value' => $value,
                    'criteria' => $operator
            );
        }
        return $crits;
    }

    /**
     * @param array $vars
     * @param string $type
     * @return array
     */
    public function create($vars, $type = null, $userid=true)
    {
        if (!$type) {
            $type = $this->type;
        }
        if ($this->parentType) {
            $type = "{$this->parentType}/{$this->parentId}/$type";
        }
        if ($userid) {
            $vars['user_id'] = $this->connection->getUserId();
        }
        return $this->connection->call($type, array(), $vars, "POST");
    }

    public function save()
    {
        $this->create(func_get_args());
    }

    public function getParentType()
    {
        return $this->parentType;
    }

    public function setParentType($parentType)
    {
        $this->parentType = $parentType;
        return $this;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
        return $this;
    }

}
