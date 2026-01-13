<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Resources\Json\JsonResource;

    class NotificationAlertResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            return [
                'admin_email' => $this->admin_email ,
                'admin_phone' => $this->admin_phone ,
                'events'      => [
                    "id"          => $this->event_key ,
                    "category"    => $this->category ,
                    "label"       => $this->label ,
                    "description" => $this->description ,
                    "channels"    => [
                        "email"    => (bool) $this->email ,
                        "sms"      => (bool) $this->sms ,
                        "whatsapp" => (bool) $this->whatsapp ,
                        "system"   => (bool) $this->system
                    ]
                ]
            ];
        }
    }
