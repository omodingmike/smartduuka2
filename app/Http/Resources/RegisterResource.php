<?php

    namespace App\Http\Resources;

    use App\Enums\PaymentType;
    use App\Libraries\AppLibrary;
    use App\Models\Order;
    use App\Models\Register;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Register */
    class RegisterResource extends JsonResource
    {
        /**
         * @param Request $request
         *
         * @mixin Register
         * @return array
         */
        public function toArray(Request $request) : array
        {
            $allProducts = $this->orders->flatMap( function ($order) {
                return $order->orderProducts;
            } );

            $groupedItems         = $allProducts->groupBy( 'item.id' )->map( function ($group) {
                $firstItem     = $group->first()->item;
                $totalQuantity = $group->sum( 'quantity' );
                $totalCost     = $totalQuantity * ( $firstItem->buying_price ?? 0 );

                return [
                    'item_id'              => $firstItem?->id ,
                    'name'                 => $firstItem?->name ,
                    'stock'                => $firstItem?->stock ,
                    'unit'                 => new UnitResource( $firstItem?->unit ) ,
                    'quantity'             => $totalQuantity ,
                    'total_sales'          => $group->sum( 'total' ) ,
                    'total_sales_currency' => AppLibrary::currencyAmountFormat( $group->sum( 'total' ) ) ,
                    'total_cost'           => $totalCost ,
                    'total_cost_currency'  => AppLibrary::currencyAmountFormat( $totalCost ) ,
                ];
            } )->values();
            $paymentSummary       = $this->posPayments->groupBy( 'payment_method_id' )->map( function ($group) {
                $methodName  = $group->first()->paymentMethod?->name ?? 'Unknown';
                $totalAmount = $group->sum( 'amount' );

                return [
                    'payment_method_id' => $group->first()->payment_method_id ,
                    'name'              => $methodName ,
                    'total'             => $totalAmount ,
                    'total_currency'    => AppLibrary::currencyAmountFormat( $totalAmount ) ,
                ];
            } )->values();
            $grandTotalCost       = $groupedItems->sum( 'total_cost' );
            $profit               = $this->posPayments()->sum( 'amount' ) - $grandTotalCost;
            $totalCreditRemaining = $this->orders
                ->where( 'payment_type' , PaymentType::CREDIT )
                ->sum( 'balance' );
            $deposits             = $this->orders()->where( 'payment_type' , '<>' , PaymentType::CASH )->get()->sum( function (Order $order) {
                return $order->posPayments()->sum( 'amount' );
            } );
            return [
                'id'                           => $this->id ,
                'opening_float'                => $this->opening_float ,
                'opening_float_currency'       => AppLibrary::currencyAmountFormat( $this->opening_float ) ,
                'notes'                        => $this->notes ,
                'status'                       => [ 'label' => $this->status->label() , 'value' => $this->status?->value ] ,
                'expected_float'               => $this->expected_float ,
                'expected_float_currency'      => AppLibrary::currencyAmountFormat( $this->expected_float ) ,
                'closing_float'                => $this->closing_float ,
                'closing_float_currency'       => AppLibrary::currencyAmountFormat( $this->closing_float ) ,
                'difference'                   => $this->difference ,
                'difference_currency'          => AppLibrary::currencyAmountFormat( $this->difference ) ,
                'closed_at'                    => $this->closed_at ,
                'created_at'                   => AppLibrary::datetime2( $this->created_at ) ,
                'user_id'                      => $this->user_id ,
                'sales'                        => $this->posPayments()->sum( 'amount' ) ,
                'sales_currency'               => AppLibrary::currencyAmountFormat( $this->posPayments()->sum( 'amount' ) ) ,
                'expense'                      => $this->expenses()->sum( 'amount' ) ,
                'expenses'                     => ExpenseResouce::collection( $this->expenses ) ,
                'expense_currency'             => AppLibrary::currencyAmountFormat( $this->expenses()->sum( 'amount' ) ) ,
                'user'                         => new UserResource( $this->whenLoaded( 'user' ) ) ,
                'posPayments'                  => PosPaymentResource::collection( $this->posPayments ) ,
                'item_summary'                 => $groupedItems ,
                'payment_summary'              => $paymentSummary ,
                'total_cost_of_goods'          => $grandTotalCost ,
                'total_credit'                 => $totalCreditRemaining ,
                'deposits'                     => $deposits ,
                'deposits_currency'            => currency( $deposits ) ,
                'total_credit_currency'        => AppLibrary::currencyAmountFormat( $totalCreditRemaining ) ,
                'total_cost_of_goods_currency' => AppLibrary::currencyAmountFormat( $grandTotalCost ) ,
                'profit'                       => $profit ,
                'profit_currency'              => AppLibrary::currencyAmountFormat( $profit ) ,
                'orders'                       => OrderResource::collection( $this->orders ) ,
            ];
        }
    }
