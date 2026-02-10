<?php

    namespace App\Jobs;

    use App\Services\TelegramService;
    use Illuminate\Contracts\Queue\ShouldQueue;
    use Illuminate\Foundation\Queue\Queueable;
    use Throwable;

    class SendExceptionJob implements ShouldQueue
    {
        use Queueable;

        public function __construct(public array $exceptionData) {}

        public function handle(TelegramService $telegramService) : void
        {
            $telegramService->send( $this->exceptionData );
        }

        public static function dispatchException(Throwable $e) : void
        {
            $data = [
                'message' => $e->getMessage() ,
                'file'    => $e->getFile() ,
                'line'    => $e->getLine() ,
                'trace'   => $e->getTraceAsString() ,
            ];
            static::dispatchAfterResponse( $data );
        }
    }
