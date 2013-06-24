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
    protected $positionArticleAPI = null;

    /**
     * @var OrderPositionStandard
     */
    protected $positionAPI = null;

    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
        $this->type = 'kb_order';
    }

    /**
     * @return OrderPositionArticle
     */
    protected function getPositionArticleAPI()
    {
        if ($this->positionArticleAPI == null) {
            $this->positionArticleAPI = new OrderPositionArticle($this->connection, $this->type, null);
        }
        return $this->positionArticleAPI;
    }

    /**
     * @return OrderPositionStandard
     */
    protected function getPositionAPI()
    {
        if ($this->positionAPI == null) {
            $this->positionAPI = new OrderPositionStandard($this->connection, $this->type, null);
        }
        return $this->positionAPI;
    }

    public function save()
    {
        return call_user_method_array('create', $this, func_get_args());
    }

    /**
     * @param Ressource $parent_id
     * @param decimal $amount
     * @param decimal $unit_price
     * @param Ressource $tax_id
     * @param Ressource $article_id
     * @param Ressource $unit_id
     * @param decimal $discount_in_percent
     * @param string $text (max 4000)
     */
    public function createPositionArticle($parent_id, $amount, $unit_price, $tax_id, $article_id, $unit_id = null, $discount_in_percent = null, $text = null)
    {
        $this->getPositionArticleAPI()->setParentId($parent_id);
        $vars = compact(array_keys(get_defined_vars()));
        return $this->getPositionArticleAPI()->create($vars);
    }


    /**
     * @param Ressource $parent_id
     * @param decimal $amount
     * @param Ressource $tax_id
     * @param decimal $unit_price
     * @param decimal $discount_in_percent
     * @param string $text (max 4000)
     * @param Ressource $unit_id
     * @return array
     */
    public function createPositionStandard($parent_id, $amount, $tax_id, $unit_price = null, $discount_in_percent = null, $text = null, $unit_id = null)
    {

        $this->getPositionAPI()->setParentId($parent_id);
        $vars = compact(array_keys(get_defined_vars()));
        return $this->getPositionAPI()->create($vars);
    }

    /**
     * @param Ressource Contact $contact_id
     * @param Ressource Contact $contact_sub_id
     * @param string $title
     * @return array
     */
    public function createNew($contact_id, $contact_sub_id = null, $title = null)
    {
        $vars = compact(array_keys(get_defined_vars()));
        $vars['mwst_type'] = $this->mwst_type;
        $vars['mwst_is_net'] = $this->mwst_is_net;
        return $this->create($vars);
    }

    public function getCurrency_id()
    {
        return $this->currency_id;
    }

    public function setCurrency_id($currency_id)
    {
        $this->currency_id = $currency_id;
        return $this;
    }

    public function getMwst_type()
    {
        return $this->mwst_type;
    }

    public function setMwst_type($mwst_type)
    {
        $this->mwst_type = $mwst_type;
        return $this;
    }

    public function getMwst_is_net()
    {
        return $this->mwst_is_net;
    }

    public function setMwst_is_net($mwst_is_net)
    {
        $this->mwst_is_net = $mwst_is_net;
        return $this;
    }

}
