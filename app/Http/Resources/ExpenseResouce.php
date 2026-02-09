<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class ExpenseResouce extends JsonResource
    {

        public function toArray(Request $request) : array
        {
            return [
                'id'                   => $this->id ,
                'name'                 => $this->name ,
                'amount'               => $this->amount ,
                'amount_currency'      => AppLibrary::currencyAmountFormat( $this->amount ) ,
                'date'                 => $this->date ? AppLibrary::datetime2( $this->date ) : '' ,
                'category'             => new ExpenseCategoryResource( $this->expenseCategory ) ,
                'user_id'              => $this->user_id ,
                'note'                 => $this->note ,
                'expense_type'         => $this->expense_type ,
                'referenceNo'          => $this->reference_no ,
                'attachment'           => $this->getFirstMediaUrl( 'attachment' ) ,
                'recurs'               => $this->recurs ,
                'repetitions'          => $this->repetitions ,
                'balance'              => 0 ,
                'balance_currency'     => AppLibrary::currencyAmountFormat( 0 ) ,
                'repeats_on'           => $this->repeats_on ,
                'paid_on'              => $this->paid_on ? AppLibrary::datetime2( $this->paid_on ) : '' ,
                'paid'                 => $this->paid ,
                'paid_currency'        => AppLibrary::currencyAmountFormat( $this->paid ) ,
                'isRecurring'          => $this->is_recurring ? 1 : 0 ,
                'baseAmount_currency'  => AppLibrary::currencyAmountFormat( $this->base_amount ) ,
                'baseAmount'           => $this->base_amount ,
                'extraCharge'          => $this->extra_charge ,
                'extraCharge_currency' => AppLibrary::currencyAmountFormat( $this->extra_charge ) ,
                'count'                => $this->count ,
                'image'                => $this->image
            ];
        }
    }
