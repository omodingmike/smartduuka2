<?php

    namespace App\Helpers;

    use AfricasTalking\SDK\AfricasTalking;

    class ATService
    {
        public function send(string $name , string $phone , string $status) : void
        {
            $username       = config('africastalking.AT_USERNAME');
            $apikey         = config('africastalking.AT_API_KEY');
            $message        = "Dear $name, your payment for QualityWIFI failed due to $status.";
            $from           = 'ATTech';
            $africasTalking = new AfricasTalking($username , $apikey);
            $sms            = $africasTalking->sms();
            $data           = [
                'to'      => $phone ,
                'message' => $message ,
                'from'    => $from
            ];
            $sms->send($data);
        }
    }
