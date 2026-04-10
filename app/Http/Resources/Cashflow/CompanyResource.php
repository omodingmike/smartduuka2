<?php

    namespace App\Http\Resources\Cashflow;


    use Illuminate\Http\Resources\Json\JsonResource;

    class CompanyResource extends JsonResource
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
                "company_name"  => $this->info[ 'company_name' ] ?? '' ,
                "company_email" => $this->info[ 'company_email' ] ?? '' ,
                "company_phone" => $this->info[ 'company_phone' ] ?? '' ,
            ];
        }
    }
