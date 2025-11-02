<?php

    namespace App\Enums;

    interface ProductionProcessStatus
    {
        const PROCESSING = 1;
        const SCHEDULED  = 2;
        const COMPLETED  = 3;
        const CANCELLED  = 4;
    }
