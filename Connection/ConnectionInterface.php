<?php
/**
 * Created by iBROWS AG.
 * User: marcsteiner
 * Date: 06.11.14
 * Time: 11:38
 */

namespace Ibrows\EasySysBundle\Connection;


/**
 * Interface ConnectionInterface
 * @package Ibrows\EasySysBundle\Connection
 */
interface ConnectionInterface {

//    public function send($relativeUri, $method, array $data = array(), $accept = 'application/json', $handler = null)
    public function call($resource, $urlParams = array(), $postParams = array(), $verb = "GET", $limit = 0, $offset = 0, $order_by = null, $getRawData = false);

} 