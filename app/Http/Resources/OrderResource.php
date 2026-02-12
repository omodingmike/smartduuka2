<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use App\Models\Order;
    use Illuminate\Http\Resources\Json\JsonResource;

    class OrderResource extends JsonResource
    {
        /**
         * Transform the resource into an array.
         * @mixin Order
         *
         * @param \Illuminate\Http\Request $request
         *
         * @return array
         */
        public function toArray($request) : array
        {
            $last_paid = $this->posPayments()?->latest()?->first();
            return [
                'id'                             => $this->id ,
                'order_serial_no'                => $this->order_serial_no ,
                'user_id'                        => $this->user_id ,
                "total_amount_price"             => AppLibrary::flatAmountFormat( $this->total ) ,
                "total_currency_price"           => AppLibrary::currencyAmountFormat( $this->total ) ,
                'status'                         => [ 'label' => $this->status?->label() , 'value' => $this->status?->value ] ,
                'order_type'                     => [ 'label' => $this->order_type?->label() , 'value' => $this->order_type?->value ] ,
                'payment_status'                 => [ 'label' => $this->payment_status?->label() , 'value' => $this->payment_status?->value ] ,
                'discount'                       => AppLibrary::currencyAmountFormat( $this->discount ) ,
                'paid'                           => $this->paid ,
                'net_paid'                       => $this->net_paid ,
                'last_paid'                      => $last_paid ? [
                    'amount'           => currency( $last_paid?->amount ?? 0 ) ,
                    'previous_balance' => currency( $this->balance + ( $last_paid?->amount ?? 0 ) ) ,
                    'method'           => $last_paid->paymentMethod
                ] : [] ,
                'net_paid_currency'              => AppLibrary::currencyAmountFormat( $this->net_paid ) ,
                'paid_currency'                  => AppLibrary::currencyAmountFormat( $this->paid ) ,
                'change'                         => AppLibrary::currencyAmountFormat( $this->change ) ,
                'balance'                        => $this->balance ,
                'balance_currency'               => AppLibrary::currencyAmountFormat( $this->balance ) ,
                'shipping_charge'                => AppLibrary::currencyAmountFormat( $this->shipping_charge ) ,
                'order_items'                    => optional( $this->orderProducts )->count() ,
                'order_datetime'                 => AppLibrary::datetime2( $this->order_datetime ) ,
                'user'                           => new OrderUserResource( $this->user ) ,
                'creator'                        => new UserResource( $this->creator ) ,
                'orderProducts1'                  => $this->orderProducts  ,
                'orderProducts'                  => OrderProductResourceNew::collection( $this->orderProducts ) ,
                'delivery_address'               => $this->delivery_address ,
                'paymentMethods'                 => PosPaymentResource::collection( $this->paymentMethods ) ,

                // Added keys from OrderDetailsResource
                "subtotal_currency_price"        => AppLibrary::currencyAmountFormat( $this->subtotal ) ,
                "tax_currency_price"             => AppLibrary::currencyAmountFormat( $this->tax ) ,
                "discount_currency_price"        => AppLibrary::currencyAmountFormat( $this->discount ) ,
                "shipping_charge_currency_price" => AppLibrary::currencyAmountFormat( $this->shipping_charge ) ,
                'original_type'                  => $this->original_type ,
                'order_date'                     => AppLibrary::date( $this->order_datetime ) ,
                'due_date'                       => $this->due_date ? AppLibrary::date( $this->due_date ) : NULL ,
                'order_time'                     => AppLibrary::time( $this->order_datetime ) ,
                'reason'                         => $this->reason ,
                'source'                         => $this->source ,
                'unit'                           => new UnitResource ( $this->unit ) ,
                'change_currency'                => AppLibrary::currencyAmountFormat( $this->change ) ,
                'active'                         => $this->active ,
                'pos_payment_method'             => $this->pos_payment_method ,
                'pos_payment_method_name'        => trans( "posPaymentMethod." . $this->pos_payment_method ) ,
                'pos_payment_note'               => $this->pos_payment_note ,
            ];
        }
    }
