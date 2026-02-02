<?php

    namespace App\Http\Resources;

    use App\Models\Printer;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\JsonResource;

    /** @mixin Printer */
    class PrinterResource extends JsonResource
    {
        public function toArray(Request $request) : array
        {
            return [
                'id'                => $this->id ,
                'name'              => $this->name ,
                'connection_type'   => $this->connection_type ,
                'profile'           => $this->profile ,
                'chars'             => $this->chars ,
                'ip'                => $this->ip ,
                'port'              => $this->port ,
                'path'              => $this->path ,
                'bluetooth_address' => $this->bluetooth_address ,
                'printJobs'         => [] ,
            ];
        }
    }
