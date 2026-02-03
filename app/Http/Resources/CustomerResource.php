<?php

    namespace App\Http\Resources;


    use App\Libraries\AppLibrary;
    use Illuminate\Http\Resources\Json\JsonResource;

    class CustomerResource extends JsonResource
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
            $totalPaid  = $this->orders->flatMap( function ($order) {
                return $order->posPayments;
            } )->sum( 'amount' );

            return [
                "id"            => $this->id ,
                "name"          => $this->name ,
                "username"      => $this->username ,
                "email"         => $this->email ,
                "type"          => $this->type ,
                "phone"         => $this->phone === NULL ? '' : $this->phone ,
                "status"        => $this->status ,
                "credits"       => AppLibrary::currencyAmountFormat( $this->credits ) ,
                "show_pay"      => $this->credits > 0 ,
                "show_pay_list" => count( $this->payments ) > 0 ,
                "image"         => $this->image ,
                "notes"         => $this->notes ,
                "totalBalance"  => AppLibrary::currencyAmountFormat( $this->orders->sum('balance') ) ,
                "totalSpent"    => AppLibrary::currencyAmountFormat($totalPaid) ,
                "addresses"     => AddressResource::collection( $this->addresses ) ,
                'orders'        => $this->orders->map( function ($order) {
                    $paid = $order->posPayments()->sum( 'amount' );
                    return [
                        'id'              => $order->id ,
                        'order_serial_no' => $order->order_serial_no ,
                        'order_datetime'  => AppLibrary::datetime2( $order->order_datetime ) ,
                        'total_amount'    => $order->total ,
                        'balance'         => $order->balance,
                        'paid_currency'   => AppLibrary::currencyAmountFormat( $paid ) ,
                        'total_currency'  => AppLibrary::currencyAmountFormat( $order->total ) ,
                        'status'          => [
                            'label' => $order->status->label() ,
                            'value' => $order->status->value
                        ] ,
                        'payment_status'  => [
                            'label' => $order->payment_status->label() ,
                            'value' => $order->payment_status->value
                        ] ,
                        'payment_type'    => [
                            'label' => $order->payment_type->label() ,
                            'value' => $order->payment_type->value
                        ] ,
                        // Summarizing items for the table row
                        'items_count'     => $order->orderProducts->sum( 'quantity' ) ,
                        'items_summary'   => $order->orderProducts->map( function ($op) {
                            return $op->item->name . ' (x' . $op->quantity . ')';
                        } )->implode( ', ' ) ,
                    ];
                } ) ,
                'creditOrders'  => $this->credit_orders->map( function ($order) {
                    $paid = $order->posPayments()->sum( 'amount' );
                    return [
                        'id'              => $order->id ,
                        'order_serial_no' => $order->order_serial_no ,
                        'order_datetime'  => AppLibrary::datetime2( $order->order_datetime ) ,
                        'total_amount'    => $order->total ,
                        'paid_currency'   => AppLibrary::currencyAmountFormat( $paid ) ,
                        'total_currency'  => AppLibrary::currencyAmountFormat( $order->total ) ,
                        'balance'         => $order->balance,
                        'balance_currency'=> AppLibrary::currencyAmountFormat( $order->balance ),
                        'status'          => [
                            'label' => $order->status->label() ,
                            'value' => $order->status->value
                        ] ,
                        'payment_status'  => [
                            'label' => $order->payment_status->label() ,
                            'value' => $order->payment_status->value
                        ] ,
                        'payment_type'    => [
                            'label' => $order->payment_type->label() ,
                            'value' => $order->payment_type->value
                        ] ,
                        // Summarizing items for the table row
                        'items_count'     => $order->orderProducts->sum( 'quantity' ) ,
                        'items_summary'   => $order->orderProducts->map( function ($op) {
                            return $op->item->name . ' (x' . $op->quantity . ')';
                        } )->implode( ', ' ) ,
                    ];
                } )->values() ,
                "creditProfile" => [] ,
                "created_at"    => AppLibrary::date( $this->created_at ) ,
            ];
        }
    }
