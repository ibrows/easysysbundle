<?php

namespace Ibrows\EasySysBundle;

use Ibrows\EasySysLibrary\Converter\AbstractConverter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Ibrows\EasySysBundle\Converter\Converter;

class IbrowsEasySysBundle extends Bundle
{
    const types = "
                article
                article_type
                bank_account
                calendar_type
                client_service
                communication_kind
                contact
                contact_branch
                contact_group
                contact_relation
                contact_type
                country
                currency
                kb_order
                kb_offer
                kb_invoice
                language
                logopaper
                monitoring
                monitoring_status
                payment_type
                salutation
                stock
                stock_place
                tax
                title
                unit
                user
                ";

    public $converter;

    public function __construct()
    {
        $this->converter = new Converter();
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        $arr = preg_split('/\s+/', trim(self::types));
        $arr2 = array();
        foreach ($arr as $type) {
            $internaltype = str_replace('kb_', '', $type);
            $arr2[$internaltype] = $type;
        }
        return $arr2;
    }

    public function boot()
    {
        $this->converter->setThrowExceptionOnAdditionalData(
            $this->isThrowExceptionOnAdditionalData($this->container)
        );
    }

    /**
     * @param ContainerInterface $container
     * @return bool
     */
    private function isThrowExceptionOnAdditionalData(ContainerInterface $container)
    {
        $throwExceptionOnAdditionalDataParameter = 'ibrows_easy_sys.throwExceptionOnAdditionalData';
        if (
            $container->hasParameter($throwExceptionOnAdditionalDataParameter) &&
            !is_null($flag = $container->getParameter($throwExceptionOnAdditionalDataParameter))
        ) {
            return (bool)$flag;
        }

        return (bool)$container->getParameter('kernel.debug');
    }
}
