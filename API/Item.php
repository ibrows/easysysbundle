<?php
/**
 * Created by PhpStorm.
 * User: cyrillgsell
 * Date: 28.10.14
 * Time: 13:31
 */

namespace Ibrows\EasySysBundle\API;

use Ibrows\EasySysBundle\Connection\Connection;

class Item extends AbstractType {

    public function __construct(Connection $connection){
        $this->connection = $connection;
        $this->type = 'article';
    }

    public function show($id){
        return $this->connection->call("$this->type/$id");
    }
} 