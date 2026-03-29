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
                'packing_slip'        => isset( $this->info[ 'packing_slip' ] ) ? (int) $this->info[ 'packing_slip' ] : 0 ,
                'purchase_order'      => isset( $this->info[ 'purchase_order' ] ) ? (int) $this->info[ 'purchase_order' ] : 0 ,
                'receipt_and_invoice' => isset( $this->info[ 'receipt_and_invoice' ] ) ? (int) $this->info[ 'receipt_and_invoice' ] : 0
            ];
        }
    }
