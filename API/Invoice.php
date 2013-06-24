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


}
