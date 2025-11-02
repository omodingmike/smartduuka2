<?php

    namespace App\Http\Requests;

    use App\Enums\Enabled;
    use App\Enums\ProductionProcessStatus;
    use Illuminate\Foundation\Http\FormRequest;
    use Smartisan\Settings\Facades\Settings;

    class StoreProductionProcessRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'setup_id'            => 'required' ,
                'quantity'            => 'required|integer' ,
                'status'              => 'required|integer' ,
                'schedule_start_date' => 'required_if:status,' . ProductionProcessStatus::SCHEDULED . '|date' ,
            ];
        }

        public function withValidator($validator) : void
        {
            $validator->after(function ($validator) {
                $module_warehouse = Settings::group('module')->get('module_warehouse');
                if ( $module_warehouse == Enabled::YES && $this->warehouse_id == 'null' ) {
                    $validator->errors()->add('warehouse_id' , 'The warehouse field is required.');
                }
            });
        }
    }
