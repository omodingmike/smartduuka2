<?php

    namespace App\Http\Resources;


    use App\Enums\PreOrderStatus;
    use App\Libraries\AppLibrary;
    use App\Models\Order;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin Order
     */
    class OrderResource extends JsonResource
    {
        public function toArray($request) : array
        {
            $last_paid = $this->posPayments()?->latest()?->first();
            $new_total = $this->orderProducts->sum( function ($product) {
                return $product->unit_price * $product->quantity;
            } );

            return [
                'id'                             => $this->id ,
                'order_serial_no'                => $this->order_serial_no ,
                'user_id'                        => $this->user_id ,
                'refund_status'                  => $this->refund_status ,
                'is_returned'                    => $this->is_returned ,
                'original_order_id'              => $this->when( $this->original_order_id , $this->originalOrder?->order_serial_no ) ,
                'return_status'                  => $this->return_status ,
                'return_type'                    => $this->return_type ,
                'currency'                       => currencySymbol() ,
                'payment_type'                   => $this->payment_type ,
                'pre_order_status'               => $this->pre_order_status ?? PreOrderStatus::PENDING_STOCK ,
                "total_amount_price"             => AppLibrary::flatAmountFormat( $this->total ) ,
                "total_currency_price"           => AppLibrary::currencyAmountFormat( $this->total ) ,
                'status'                         => [ 'label' => $this->status?->label() , 'value' => $this->status?->value ] ,
                'order_type'                     => [ 'label' => $this->order_type?->label() , 'value' => $this->order_type?->value ] ,
                'payment_status'                 => [ 'label' => $this->payment_status?->label() , 'value' => $this->payment_status?->value ] ,
                'discount'                       => AppLibrary::currencyAmountFormat( $this->discount ) ,
                'paid'                           => $this->paid ,
                'quotation_status'               => $this->quotation_status ,
                'net_paid'                       => $this->net_paid ,
                'last_paid'                      => $last_paid ? [
                    'amount'           => currency( $last_paid?->amount ?? 0 ) ,
                    'previous_balance' => currency( $this->balance + ( $last_paid?->amount ?? 0 ) ) ,
                    'method'           => $last_paid->paymentMethod
                ] : NULL ,
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
                'orderProducts'                  => OrderProductResourceNew::collection( $this->orderProducts ) ,
                'delivery_address'               => $this->delivery_address ,
                'paymentMethods'                 => PosPaymentResource::collection( $this->paymentMethods ) ,

                // Added keys from OrderDetailsResource
                "subtotal_currency_price"        => AppLibrary::currencyAmountFormat( $this->subtotal ) ,
                "tax_currency_price"             => AppLibrary::currencyAmountFormat( $this->tax ) ,
                "discount_currency_price"        => AppLibrary::currencyAmountFormat( $this->discount ) ,
                "shipping_charge_currency_price" => AppLibrary::currencyAmountFormat( $this->shipping_charge ) ,
//                'original_type'                  => $this->original_type ,
                'order_date'                     => AppLibrary::date( $this->order_datetime ) ,
                'due_date'                       => $this->due_date ? AppLibrary::date( $this->due_date ) : NULL ,
                'order_time'                     => AppLibrary::time( $this->order_datetime ) ,
                'reason'                         => $this->reason ,
                'source'                         => $this->source ,
                'expiry_date'                    => $this->due_date ? datetime( $this->due_date ) : '' ,
                'unit'                           => new UnitResource ( $this->unit ) ,
                'change_currency'                => AppLibrary::currencyAmountFormat( $this->change ) ,
//                'active'                         => $this->active ,
                'pos_payment_method'             => $this->pos_payment_method ,
                'pos_payment_method_name'        => trans( "posPaymentMethod." . $this->pos_payment_method ) ,
                'pos_payment_note'               => $this->pos_payment_note ,
                'new_total'                      => $new_total ,
                'difference'                     => $new_total - $this->total ,
                'difference_currency'            => currency( $new_total - $this->total ) ,
            ];
        }
    }
