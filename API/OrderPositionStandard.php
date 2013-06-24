<?php
namespace Ibrows\EasySysBundle\API;
use Ibrows\EasySysBundle\Connection\Connection;
/**
 * @author marcsteiner
 *
 */

class OrderPositionStandard extends AbstractType
{

    public function __construct(Connection $connection, $parentType, $parentId)
    {
        parent::__construct($connection);
        $this->type = 'kb_position_custom';
        $this->parentType = $parentType;
        $this->parentId = $parentId;
    }

}
