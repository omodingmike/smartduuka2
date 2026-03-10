<?php

    namespace App\Http\Resources;

    use App\Enums\ExpenseNature;
    use App\Enums\PaymentType;
    use App\Enums\PosPaymentType;
    use App\Libraries\AppLibrary;
    use App\Models\ExpensePayment;
    use App\Models\Order;
    use App\Models\ProductVariation;
    use App\Models\Register;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Str;

    /** @mixin Register */
    class RegisterResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $allProducts = $this->orders->flatMap( function ($order) {
                return $order->orderProducts;
            } );

            $groupedItems   = $allProducts->groupBy( function ($item) {
                return $item->item_id . '-' . $item->item_type;
            } )->map( function ($group) {
                $firstItem       = $group->first()->item;
                $totalQuantity   = $group->sum( 'quantity' );
                $quantity_picked = $group->sum( 'quantity_picked' );
                $totalCost       = $totalQuantity * ( $firstItem->buying_price ?? 0 );

                $name = $firstItem?->name;
                if ( $firstItem instanceof ProductVariation ) {
                    $firstItem->loadMissing( 'productAttributeOption.productAttribute' );
                    if ( $firstItem->productAttributeOption ) {
                        $name = $firstItem->product->name . ' - ' . $firstItem->productAttributeOption->productAttribute->name . ' (' . $firstItem->productAttributeOption->name . ')';
                    }
                }
                $reserved = $totalQuantity - $quantity_picked;
                $damages  = abs( $firstItem?->damages()->sum( 'quantity' ) ?? 0 );

                return [
                    'item_id'              => $firstItem?->id ,
                    'name'                 => $name ,
                    'damages'              => $damages ,
                    'damages_value'        => $damages * ( $firstItem->buying_price ?? 0 ) ,
                    'stock'                => $firstItem?->stock ,
                    'reserved'             => $reserved ,
                    'reserved_value'       => $reserved * ( $firstItem->buying_price ?? 0 ) ,
                    'unit'                 => new UnitResource( $firstItem?->unit ) ,
                    'quantity'             => $totalQuantity ,
                    'total_sales'          => $group->sum( 'total' ) ,
                    'total_sales_currency' => AppLibrary::currencyAmountFormat( $group->sum( 'total' ) ) ,
                    'total_cost'           => $totalCost ,
                    'total_cost_currency'  => AppLibrary::currencyAmountFormat( $totalCost ) ,
                ];
            } )->values();
            $paymentSummary = $this->posPayments->groupBy( 'payment_method_id' )->map( function ($group) {
                $methodName  = $group->first()->paymentMethod?->name ?? 'Unknown';
                $totalAmount = $group->sum( 'amount' );

                return [
                    'payment_method_id' => $group->first()->payment_method_id ,
                    'name'              => $methodName ,
                    'total'             => $totalAmount ,
                    'total_currency'    => AppLibrary::currencyAmountFormat( $totalAmount ) ,
                ];
            } )->values();

            $grandTotalCost = $groupedItems->sum( 'total_cost' );
            $reserved_value = $groupedItems->sum( 'reserved_value' );
            $damages_value  = $groupedItems->sum( 'damages_value' );
            $total_revenue  = $this->posPayments()->sum( 'amount' );
            $profit         = $total_revenue - $grandTotalCost;

            $expenses_items = $this->expensesPayments->map( function (ExpensePayment $expense_payment) {
                return $expense_payment->expense;
            } )->filter( function ($expense) {
                return $expense && ($expense->expense_nature === ExpenseNature::OPERATIONAL);
            } )->unique( 'id' )->values();

            $expenses = $this->expensesPayments->sum( function (ExpensePayment $expense_payment) {
                if ( $expense_payment->expense && ($expense_payment->expense->expense_nature === ExpenseNature::OPERATIONAL->value) ) {
                    return $expense_payment->amount;
                }
                return 0;
            } );

            $net_profit = $profit - $expenses;

            $totalCreditRemaining = $this->orders
                ->where( 'payment_type' , PaymentType::CREDIT )
                ->sum( 'balance' );
            $deposits             = $this->orders()->where( 'payment_type' , '<>' , PaymentType::CASH )->get()->sum( function (Order $order) {
                return $order->posPayments()->sum( 'amount' );
            } );

            $total_order_cost = $this->orders->sum( function (Order $order) {
                return $order->totalCost();
            } );

            return [
//                'id'                           => $this->id ,
                'id'                           => 'REG-' . Str::padLeft( $this->id , 5 , '0' ) ,
                'opening_float'                => $this->opening_float ,
                'reserved_value'               => currency( $reserved_value ) ,
                'damages_value'                => currency( $damages_value ) ,
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
                'expense'                      => $expenses ,
                'expenses'                     => ExpenseResource::collection( $expenses_items ) ,
                'expense_currency'             => currency( $expenses ) ,
                'user'                         => new UserResource( $this->whenLoaded( 'user' ) ) ,
                'posPayments'                  => PosPaymentResource::collection( $this->posPayments ) ,
                'item_summary'                 => $groupedItems ,
                'payment_summary'              => $paymentSummary ,
                'total_cost_of_goods'          => $grandTotalCost ,
                'total_credit'                 => $totalCreditRemaining ,
                'total_debt_paid'              => currency( $this->posPayments()->where( 'pos_payment_type' , PosPaymentType::DEBT )->sum( 'amount' ) ) ,
                'deposits'                     => $deposits ,
                'deposits_currency'            => currency( $deposits ) ,
                'total_credit_currency'        => AppLibrary::currencyAmountFormat( $totalCreditRemaining ) ,
                'total_cost_of_goods_currency' => AppLibrary::currencyAmountFormat( $grandTotalCost ) ,
                'profit'                       => $profit ,
                'profit_currency'              => AppLibrary::currencyAmountFormat( $profit ) ,
                'orders'                       => OrderResource::collection( $this->orders ) ,
                'total_order_cost'             => $total_order_cost ,
                'total_revenue'                => $total_revenue ,
                'net_profit'                   => $net_profit ,
                'net_profit_currency'          => currency( $net_profit ) ,
                'total_revenue_currency'       => currency( $total_revenue ) ,
            ];
        }
    }
