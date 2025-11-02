<?php

    namespace App\Http\Resources\DistributionRoute;

    use App\Libraries\AppLibrary;
    use App\Models\Stock;
    use App\Services\CommissionCalculator;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Str;

    class DistributionRouteResource extends JsonResource
    {

        public function toArray($request) : array
        {
            return [
                'id'                  => $this->id ,
                'reference'           => 'SD' . Str::padLeft( $this->id , 5 , '0' ) ,
                'date'                => AppLibrary::datetime2( $this->created_at ) ,
                'date_in'             => AppLibrary::datetime2( $this->updated_at ) ,
                'distributor'         => $this->distributor ,
                'stocks'              => $this->stocks ,
                'stockSold'           => $this->stockSold ,
                'commission'          => AppLibrary::currencyAmountFormat( $this->stockSold->sum( function (Stock $stock) {
                    return ( new CommissionCalculator )->calculateForStock( $stock );
                } ) ) ,
                'stockReturned'       => $this->stockReturned ,
                'label'               => $this->status->label() ,
                'distribution_status' => $this->status ,
                'sales'               => AppLibrary::currencyAmountFormat( $this->actual_sales ) ,
                'value'               => AppLibrary::currencyAmountFormat( $this->route_value ) ,
            ];
        }
    }
