<?php

    namespace App\Console\Commands;

    use App\Http\Controllers\WhatsAppController;
    use Illuminate\Console\Attributes\Description;
    use Illuminate\Console\Attributes\Signature;
    use Illuminate\Console\Command;

    #[Signature( 'create-whatsapp-template' )]
    #[Description( 'Command description' )]
    class CreateWhatsappTemplate extends Command
    {
        public function handle(WhatsAppController $whatsapp) : void
        {
            $res = $whatsapp->createQuotationTemplate();
            info( $res );
        }
    }
