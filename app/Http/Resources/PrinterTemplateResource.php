<?php

    namespace App\Http\Resources;

    use App\Models\PrinterTemplate;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin PrinterTemplate */
    class PrinterTemplateResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'         => $this->id ,
                'label'      => $this->label ,
                'value'      => $this->value
            ];
        }
    }
