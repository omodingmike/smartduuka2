<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\Order;
    use Illuminate\Http\Resources\Json\JsonResource;

    class OrderDetailsResource extends JsonResource
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
            $balance = $this->total - $this->paid;
            return [
                'id'                             => $this->id ,
                'order_serial_no'                => $this->order_serial_no ,
                'user_id'                        => $this->user_id ,
                "subtotal_currency_price"        => AppLibrary::currencyAmountFormat( $this->subtotal ) ,
                "tax_currency_price"             => AppLibrary::currencyAmountFormat( $this->tax ) ,
                "discount_currency_price"        => AppLibrary::currencyAmountFormat( $this->discount ) ,
                "total_currency_price"           => AppLibrary::currencyAmountFormat( $this->total ) ,
                "total_amount_price"             => AppLibrary::flatAmountFormat( $this->total ) ,
                "shipping_charge_currency_price" => AppLibrary::currencyAmountFormat( $this->shipping_charge ) ,
                'order_type'                     => $this->order_type ,
                'date_format'                    => phpToDateFnsFormat( config( 'system.date_format' ) ) ,
                'original_type'                  => $this->original_type ,
                'paymentMethods'                 => OrderPaymentMethodResource::collection( $this->posPayments ) ,
                'stocks'                         => $this->stocks ,
                'order_date'                     => AppLibrary::date( $this->order_datetime ) ,
                'due_date'                       => $this->due_date ? AppLibrary::date( $this->due_date ) : NULL ,
                'order_time'                     => AppLibrary::time( $this->order_datetime ) ,
                'order_datetime'                 => AppLibrary::datetime( $this->order_datetime ) ,
                'balance'                        => max( $balance , 0 ) ,
                'balance_currency'               => AppLibrary::currencyAmountFormat( max( $balance , 0 ) ) ,
                'payment_method'                 => $this->payment_method ,
                'payment_method_name'            => $this->paymentMethod?->name ,
                'payment_status'                 => $this->payment_status ,
                'status'                         => $this->status ,
                'label'                          => orderLabel( Order::find( $this->id ) ) ,
                'label1'                         => match ( TRUE ) {
                    $this->payment_status == 10 && $this->status == 5 => 'Invoice' ,
                    $this->status == 1                                => 'Quotation' ,
                    default                                           => 'Receipt' ,
                } ,
                'reason'                         => $this->reason ,
                'source'                         => $this->source ,
                'unit'                           => new UnitResource ( $this->unit ) ,
                'discount'                       => $this->discount ,
                'paid'                           => AppLibrary::currencyAmountFormat( $this->paid ) ,
                'change'                         => AppLibrary::currencyAmountFormat( $this->change ) ,
                'change_currency'                => AppLibrary::currencyAmountFormat( $this->change ) ,
                'active'                         => $this->active ,
                'user'                           => new UserResource( $this->user ) ,
                'orderProducts'                 => OrderProductResourceNew::collection( $this->orderProducts ) ,
                'pos_payment_method'             => $this->pos_payment_method ,
                'pos_payment_method_name'        => trans( "posPaymentMethod." . $this->pos_payment_method ) ,
                'pos_payment_note'               => $this->pos_payment_note ,
            ];
        }
    }
