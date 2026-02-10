<?php

    namespace App\Services;

    use Illuminate\Support\Facades\Http;

    class TelegramService
    {
        private string $token;
        private string $chat_id;
        private string $project;
        private string $message;

        public function __construct()
        {
            $this->token   = config( 'telegram.token' );
            $this->chat_id = config( 'telegram.chat_id' );
            $this->project = 'Smartduuka';
        }

        public function send(array $throwable) : void
        {
            $this->setMessage( $throwable );
            Http::post( "https://api.telegram.org/bot{$this->token}/sendMessage" , [
                'chat_id' => $this->chat_id ,
                'text'    => $this->message ,
            ] );
        }

        private function setMessage(array $data) : void
        {
            $message       = strtoupper( $this->project ) . ' Exception Report' . PHP_EOL . PHP_EOL .
                'Message: ' . $data[ 'message' ] . PHP_EOL .
                'File: ' . ( $data[ 'file' ] ?? '-' ) . PHP_EOL .
                'Line: ' . ( $data[ 'line' ] ?? '-' ) . PHP_EOL;
            $this->message = $message;
        }
    }
