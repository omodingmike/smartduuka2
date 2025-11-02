<?php

namespace App\Http\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseCategoryDepthTreeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'thumb'   => $this->thumb,
            'cover'   => $this->cover,
            'option'  => $this->segment($this->depth).' '.$this->name,
            'segment' => $this->segment($this->depth),
        ];
    }

    private function segment($depth): string
    {
        $segment = '';
        $loop = 1;
        if ($depth > 0) {
            while ($loop <= $depth) {
                $segment .= '-';
                $loop++;
            }
        }
        return $segment;
    }
}
