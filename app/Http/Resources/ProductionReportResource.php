<?php

    namespace App\Http\Resources;

    use App\Libraries\AppLibrary;
    use App\Models\ProductionSetup;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Illuminate\Support\Str;

    class ProductionReportResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                  => Str::padLeft($this->id , 5 , 0) ,
                'setup'               => [
                    'id'   => Str::padLeft($this->setup->id , 4 , 0) ,
                    'name' => $this->setup->name ,
                ] ,
                'quantity'            => number_format($this->quantity) ,
                'production_cost'     => AppLibrary::currencyAmountFormat($this->setup->overall_cost) ,
                'production_value'    => AppLibrary::currencyAmountFormat($this->productionValue($this->setup)) ,
                'actual_quantity'     => number_format($this->actual_quantity) ,
                'status'              => $this->status ,
                'damage_type'         => $this->damage_type ,
                'damage_reason'       => $this->damage_reason ,
                'damage_result'       => $this->damage_result ,
                'start_date'          => AppLibrary::date($this->start_date) ,
                'schedule_start_date' => $this->schedule_start_date ? AppLibrary::date($this->schedule_start_date) : 'NA' ,
                'end_date'            => $this->end_date ? AppLibrary::date($this->end_date) : 'NA' ,
            ];
        }

        private function productionValue(ProductionSetup $productionSetup)
        {
            return $productionSetup->item?->price * $this->actual_quantity;
        }
    }
