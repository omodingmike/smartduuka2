<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    class CommissionSummaryResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            $payouts = $this->payouts->sum( 'amount' );
            return [
                'id'         => $this->id ,
                'name'       => $this->name ,
                'balance'    => AppLibrary::currencyAmountFormat( $this->commission - $payouts ) ,
                'payouts'    => AppLibrary::currencyAmountFormat( $payouts ) ,
                'commission' => AppLibrary::currencyAmountFormat( $this->commission ) ,
                'sales'      => AppLibrary::currencyAmountFormat( $this->sales ) ,
            ];
        }
    }
