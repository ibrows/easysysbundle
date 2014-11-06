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
interface ConnectionInterface
{

    public function call($resource, $urlParams = array(), $postParams = array(), $verb = "GET", $limit = 0, $offset = 0, $order_by = null, $getRawData = false);

    public function setServiceUri();

    public function setCompanyName();

    public function setApiKey();

    public function setSignatureKey();

    public function setUserId();
}