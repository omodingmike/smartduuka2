<?php

    namespace App\Http\Requests;

    use App\Enums\ProductionProcessStatus;
    use Illuminate\Foundation\Http\FormRequest;

    class UpdateProductionProcessRequest extends FormRequest
    {
        public function authorize() : bool
        {
            return true;
        }

        public function rules() : array
        {
            return [
                'status'          => 'required|numeric' ,
                'actual_quantity' => 'required_if:status,' . ProductionProcessStatus::COMPLETED . '|numeric' ,
                'quantity'        => 'required_unless:status,' . ProductionProcessStatus::COMPLETED . '|numeric' ,
                'setup_id'        => 'required_unless:status,' . ProductionProcessStatus::COMPLETED . '|numeric' ,
                'damage_type'     => 'sometimes|string' ,
                'damage_result'   => 'sometimes|numeric' ,
                'damage_reason'   => 'sometimes|string' ,
            ];
        }
    }
