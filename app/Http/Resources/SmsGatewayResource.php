<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SmsGatewayResource extends JsonResource
{
    public array $info;

    public function __construct($info)
    {
        parent::__construct($info);
        $this->info = $info;
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'sms_gateway' => $this->info['sms_gateway'] ?? env('SMS_GATEWAY'),
            'at_username' => $this->info['at_username'] ?? env('AT_USERNAME'),
            'at_apikey'   => $this->info['at_apikey'] ?? env('AT_APIKEY'),
            'status'      => $this->info['status'] ?? null,
        ];
    }
}
