<?php

    namespace App\Jobs;

    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;
    use Illuminate\Support\Facades\Artisan;

    class UpdateConfigJob implements ShouldQueue
    {
        use Queueable;

        public function __construct() {}

        public function handle() : void
        {
            Artisan::call( 'config:cache' );
            Artisan::call( 'queue:restart' );
        }
    }
