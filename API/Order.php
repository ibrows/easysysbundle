<?php
namespace Ibrows\EasySysBundle\API;

use Ibrows\EasySysBundle\Connection\Connection;


/**
 * @author marcsteiner
 *
 */
class Order extends AbstractType
{

    const MWST_TYPE_INCLUSIVE = 0;
    const MWST_TYPE_EXCLUSIVE = 1;
    const MWST_TYPE_FREE = 2;

    protected $currency_id = 2;
    protected $mwst_type = self::MWST_TYPE_INCLUSIVE;
    protected $mwst_is_net = false;

    /**
     * @var OrderPositionArticle
     */
    protected $positionAPI = null;

    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->type = 'kb_order';
    }

    /**
     * @return \Ibrows\EasySysBundle\API\OrderPositionArticle
     */
    protected function getPositionAPI()
    {
        if ($this->positionAPI == null) {
            $this->positionAPI = new OrderPositionArticle($this->connection, $this->type, null);
        }
        return $this->positionAPI;
    }

    public function save()
    {
        return call_user_method_array('createOrder', $this, func_get_args());
    }

    public function createOrderPositionArticle($parent_id, $amount, $unit_price, $tax_id, $article_id, $unit_id=null, $discount_in_percent=null, $text=null, $is_optional=null)
    {
        $this->getPositionAPI()->setParentId($parent_id);
        $vars = compact(array_keys(get_defined_vars()));
        $this->getPositionAPI()->create($vars);
    }

    public function createOrder($contact_id, $contact_sub_id = null, $title = null)
    {
        $vars = compact(array_keys(get_defined_vars()));
        $vars['mwst_type'] = $this->mwst_type;
        $vars['mwst_is_net'] = $this->mwst_is_net;
        return $this->create($vars);
    }

}
