<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\User;
    use Carbon\Carbon;
    use Illuminate\Http\Resources\Json\JsonResource;

    /**
     * @mixin User
     */
    class CustomerResource extends JsonResource
    {
        private Carbon $_now;

        public function __construct($resource)
        {
            parent::__construct( $resource );
            $this->_now = now();
        }

        public function toArray($request) : array
        {
            $creditOrders = $this->whenLoaded(
                'creditAndDeposit' ,
                function () {
                    return $this->creditAndDeposit
                        ->sortByDesc( 'id' )
                        ->map( function ($order) {
                            $paid       = $order->total_paid ?? 0;
                            $itemsCount = $order->items_count ?? 0;

                            return [
                                'id'               => $order->id ,
                                'order_serial_no'  => $order->order_serial_no ,
                                'order_datetime'   => datetime( $order->order_datetime ) ,
                                'age'              => round( Carbon::parse( $order->order_datetime )->diffInHours( $this->_now ) / 24 ) ,
                                'total_amount'     => $order->total ,
                                'paid_currency'    => AppLibrary::currencyAmountFormat( $paid ) ,
                                'total_currency'   => AppLibrary::currencyAmountFormat( $order->total ) ,
                                'balance'          => $order->balance ,
                                'balance_currency' => AppLibrary::currencyAmountFormat( $order->balance ) ,
                                'status'           => [
                                    'label' => $order->status->label() ,
                                    'value' => $order->status->value ,
                                ] ,
                                'payment_status'   => [
                                    'label' => $order->payment_status->label() ,
                                    'value' => $order->payment_status->value ,
                                ] ,
                                'payment_type'     => [
                                    'label' => $order->payment_type->label() ,
                                    'value' => $order->payment_type->value ,
                                ] ,
                                'items_count'      => $itemsCount ,
                                'items_summary'    => $order->orderProducts
                                    ->map( fn($op) => ( $op->item?->name ?? '?' ) . ' (x' . $op->quantity . ')' )
                                    ->implode( ', ' ) ,
                            ];
                        } )
                        ->values();
                } ,
                []
            );

            return [
                'id'                  => $this->id ,
                'name'                => ucwords( $this->name ) ,
                'username'            => $this->username ,
                'email'               => $this->email ,
                'type'                => $this->type ,

                // -------------------------------------------------------------------------
                // OPTIMIZATION: wallet is now a DB-computed column (addSelect subquery)
                // returned as a scalar. No walletTransactions collection needed on the list.
                // -------------------------------------------------------------------------
                'wallet'              => $this->wallet ?? 0 ,
                'wallet_currency'     => currency( $this->wallet ?? 0 ) ,

                // walletTransactions collection is only loaded in show(), so this renders
                // an empty collection on the list view without triggering any extra queries.
                'wallet_transactions' => CustomerWalletTransactionResource::collection(
                    $this->whenLoaded( 'walletTransactions' )
                ) ,

                // -------------------------------------------------------------------------
                // OPTIMIZATION: debt_paid is now a DB-computed column.
                // Before: $this->debtPayments->sum('amount') — iterated a PHP collection.
                // After:  $this->debt_paid — already summed by the DB in the list query.
                // Falls back to collection sum for show() where debt_paid isn't a column.
                // -------------------------------------------------------------------------
                'debtPaid'            => currency(
                    $this->debt_paid ?? $this->debtPayments->sum( 'amount' )
                ) ,

                // -------------------------------------------------------------------------
                // OPTIMIZATION: total_credit_orders is now a DB-computed column.
                // Before: User::$totalCreditOrders ran creditOrdersQuery()->sum('total')
                //         per customer — one extra query per row on the list.
                // After:  $this->total_credit_orders reads the pre-computed column.
                // Falls back to the attribute for show() where it isn't pre-computed.
                // -------------------------------------------------------------------------
                'totalCreditOrders'   => currency(
                    $this->total_credit_orders ?? $this->totalCreditOrders
                ) ,

                'debtPayments' => CustomerPaymentResource::collection(
                    $this->whenLoaded( 'debtPayments' , fn() => $this->debtPayments )
                ) ,

                'phone'            => $this->phone ?? '' ,
                'status'           => $this->status ,

                // credits is now a DB-computed column on list; falls back to PHP attribute
                // on show() where the model is loaded without addSelect subqueries.
                'credits'          => $this->getRawOriginal( 'credits' ) ?? $this->credits ,
                'credits_currency' => currency( $this->getRawOriginal( 'credits' ) ?? $this->credits ) ,

                'ledgers'             => CustomerLedgerResource::collection(
                    $this->whenLoaded( 'ledgers' )
                ) ,
                'show_pay'            => ( $this->getRawOriginal( 'credits' ) ?? $this->credits ) > 0 ,
                'show_pay_list'       => $this->whenLoaded(
                    'payments' ,
                    fn() => $this->payments->isNotEmpty() ,
                    FALSE
                ) ,
                'image'               => $this->image ,
                'notes'               => $this->notes ,
                'oldest_credit_order' => $this->oldest_credit_order ,
                'totalBalance'        => currency( $this->getRawOriginal( 'credits' ) ?? $this->credits ) ,
                'totalSpent'          => currency( $this->total_spent ?? 0 ) ,
                'addresses'           => AddressResource::collection(
                    $this->whenLoaded( 'addresses' )
                ) ,
                'creditOrders'        => $creditOrders ,
                'creditProfile'       => [] ,
                'created_at'          => AppLibrary::date( $this->created_at ) ,
            ];
        }
    }