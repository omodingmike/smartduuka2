<?php

    namespace App\Http\Resources;

    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class DocumentPreferenceResource extends JsonResource
    {
        public $info;

        public function __construct($info)
        {
            parent::__construct( $info );
            $this->info = $info;
        }

        public function toArray(Request $request) : array
        {
            return [
                'packing_slip'   => $this->info[ 'packing_slip' ] ? (int) $this->info[ 'packing_slip' ] : 0 ,
                'purchase_order' => $this->info[ 'purchase_order' ] ? (int) $this->info[ 'purchase_order' ] : 0
            ];
        }
    }
