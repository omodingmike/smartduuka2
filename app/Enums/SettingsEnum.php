<?php

    namespace App\Enums;

    use ArchTech\Enums\InvokableCases;

    enum SettingsEnum: string
    {
        use InvokableCases;
        case APP_SETTINGS = 'app_settings';
        case A_4_RECEIPT  = 'a4_receipt';
    }
