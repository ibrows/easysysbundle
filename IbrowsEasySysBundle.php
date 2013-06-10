<?php

namespace Ibrows\EasySysBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

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

    public static function getTypes()
    {
        $arr = preg_split('/\s+/', trim(self::types))  ;
        $arr2  = array();
        foreach($arr as $type){
            $internaltype = str_replace('kb_', '', $type);
            $arr2[$internaltype] = $type;
        }
        return $arr2;
    }



}
