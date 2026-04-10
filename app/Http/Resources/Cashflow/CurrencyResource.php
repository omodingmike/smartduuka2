<?php

    namespace App\Http\Resources\Cashflow;

    use App\Models\Currency;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;
    use Smartisan\Settings\Facades\Settings;

    /** @mixin Currency */
    class CurrencyResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'      => $this->id ,
                'name'    => $this->name ,
                'symbol'  => $this->symbol ,
                'foreign' => $this->foreign ,
                'default' => Settings::get( 'currency' ) == $this->id
            ];
        }
    }
