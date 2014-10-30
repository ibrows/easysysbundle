<?php
namespace Ibrows\EasySysBundle\API;

use Ibrows\EasySysBundle\Connection\Connection;
/**
 * @author marcsteiner
 *
 */
class Invoice extends Order
{

    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->type = 'kb_invoice';
    }

    public function markAsInvoiced($id){
    	return $this->connection->call("$this->type/$id/issue", array(), array(), "POST");
    }

    public function markAsPayed($id, $value){
        return $this->connection->call("$this->type/$id/payment", array(), array('value' => $value), "POST");
    }

    public function show($id){
        return $this->connection->call("$this->type/$id");
    }
}
