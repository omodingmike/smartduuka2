<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Resources\Json\JsonResource;

    class SiteResource extends JsonResource
    {
        public $info;

        public function __construct($info)
        {
            parent::__construct( $info );
            $this->info = $info;
        }

        public function toArray($request) : array
        {
            return [
                'site_date_format'               => $this->info['site_date_format'] ?? 'Y-m-d',
                'site_time_format'               => $this->info['site_time_format'] ?? 'H:i',
                // Wrap the check in parentheses before casting to (int)
                'site_default_timezone'          => (int) ($this->info['site_default_timezone'] ?? 0),
                'site_default_currency'          => (int) ($this->info['site_default_currency'] ?? 0),
                'site_default_branch'            => (int) ($this->info['site_default_branch'] ?? 0),
                'site_copyright'                 => $this->info['site_copyright'] ?? '',
                'site_digit_after_decimal_point' => (int) ($this->info['site_digit_after_decimal_point'] ?? 2),
                'site_default_language'          => (int) ($this->info['site_default_language'] ?? 1),
                'site_google_map_key'            => $this->info['site_google_map_key'] ?? '',
                'site_currency_position'         => (int) ($this->info['site_currency_position'] ?? 0),
                'site_email_verification'        => (int) ($this->info['site_email_verification'] ?? 0),
                'site_phone_verification'        => (int) ($this->info['site_phone_verification'] ?? 0),
                'site_online_payment_gateway'    => (int) ($this->info['site_online_payment_gateway'] ?? 0),
                'currency'                       => currencySymbol(),
            ];
        }
    }