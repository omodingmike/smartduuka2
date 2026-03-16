<?php

    namespace App\Enums;

    interface Role
    {
        const ADMIN        = 'Admin';
        const CUSTOMER     = 'Customer';
        const MANAGER      = 'Manager';
        const POS_OPERATOR = 'POS Operator';
        const STAFF        = 'Staff';
        const DISTRIBUTOR  = 'Distributor';
    }
