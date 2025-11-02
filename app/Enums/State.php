<?php

    namespace App\Enums;

    interface State
    {
        const AWAITING_BUSINESS_ID_RENEW     = 'awaiting_business_id_renew';
        const AWAITING_BUSINESS_ID_ADD       = 'awaiting_business_id_add';
        const AWAITING_PLAN_ADD              = 'awaiting_plan_add';
        const AWAITING_BUSINESS_ID_UPGRADE   = 'awaiting_business_id_upgrade';
        const AWAITING_PAYMENT_PHONE_RENEW   = 'awaiting_payment_phone_renew';
        const AWAITING_PAYMENT_PHONE_ADD     = 'awaiting_payment_phone_add';
        const AWAITING_PLAN_UPGRADE          = 'awaiting_plan_upgrade';
        const AWAITING_PAYMENT_PHONE_UPGRADE = 'awaiting_payment_phone_upgrade';
    }
