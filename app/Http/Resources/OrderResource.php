<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\Order;
    use Illuminate\Http\Resources\Json\JsonResource;

    class OrderResource extends JsonResource
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
                'id'                   => $this->id ,
                'order_serial_no'      => $this->order_serial_no ,
                'user_id'              => $this->user_id ,
                "total_amount_price"   => AppLibrary::flatAmountFormat( $this->total ) ,
                "total_currency_price" => AppLibrary::currencyAmountFormat( $this->total ) ,
                'status'               => [ 'label' => $this->status?->label() , 'value' => $this->status?->value ] ,
                'order_type'           => [ 'label' => $this->order_type?->label() , 'value' => $this->order_type?->value ] ,
                'payment_status'       => [ 'label' => $this->payment_status?->label() , 'value' => $this->payment_status?->value ] ,
                'label'                => orderLabel( Order::find( $this->id ) ) ,
                'discount'             => AppLibrary::currencyAmountFormat( $this->discount ) ,
                'paid'                 => $this->paid ,
                'paid_currency'        => AppLibrary::currencyAmountFormat( $this->paid ) ,
                'change'               => AppLibrary::currencyAmountFormat( $this->change ) ,
                'balance'              => $this->total - $this->paid < 0 ? 0 : AppLibrary::currencyAmountFormat( $this->total - $this->paid ) ,
                'shipping_charge'      => AppLibrary::currencyAmountFormat( $this->shipping_charge ) ,
                'order_items'          => optional( $this->orderProducts )->count() ,
                'order_datetime'       => AppLibrary::datetime2( $this->order_datetime ) ,
                'user'                 => new OrderUserResource( $this->user ) ,
                'creator'              => new UserResource( $this->creator ) ,
                'orderProducts'        => OrderProductResource::collection( $this->orderProducts ) ,
//                'orderProducts'        => $this->orderProducts ,
                'delivery_address'     => $this->delivery_address ,
                'paymentMethods'       => PosPaymentResource::collection( $this->paymentMethods ) ,
            ];
        }
    }
