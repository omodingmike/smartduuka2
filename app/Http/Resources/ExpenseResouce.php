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
                'id'            => $this->id ,
                'name'          => $this->name ,
                'amount'        => AppLibrary::currencyAmountFormat($this->amount) ,
                'date'          => $this->date ? AppLibrary::datetime2($this->date) : '' ,
                'category'      => $this->expenseCategory ,
                'user_id'       => $this->user_id ,
                'note'          => $this->note ,
                'paymentMethod' => $this->paymentMethod ,
                'referenceNo'   => $this->referenceNo ,
                'attachment'    => $this->attachment ,
                'recurs'        => $this->recurs ,
                'repetitions'   => $this->repetitions ,
                'repeats_on'    => $this->repeats_on ,
                'paid_on'       => $this->paid_on ? AppLibrary::datetime2($this->paid_on) : '' ,
                'paid'          => AppLibrary::currencyAmountFormat($this->paid) ,
                'isRecurring'   => $this->isRecurring ,
                'created_at'    => $this->created_at ,
                'updated_at'    => $this->updated_at ,
                'count'         => $this->count
            ];
        }
    }
