<?php
namespace Ibrows\EasySysBundle\API;

use Ibrows\EasySysBundle\Connection\Connection;

/**
 * @author dominikzogg
 *
 */
class Monitoring extends AbstractType
{
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->type = 'monitoring';
    }

    public function save()
    {
        return call_user_func_array(array($this, 'createMonitoring'), func_get_args());
    }

    public function createMonitoring($user_id, $date, $client_service_id, $duration, $allowable_bill = true, $tracking_type = 0)
    {
        $vars = compact(array_keys(get_defined_vars()));
        return $this->create($vars);
    }
}