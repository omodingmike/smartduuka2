<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PrinterTemplateSeeder extends Seeder
{
    public function run() : void
    {
        if ( DB::table( 'printer_templates' )->count() === 0 ) {
            DB::table( 'printer_templates' )->insert( [
                // Thermal Documents
                [ 'label' => 'Sales Receipt' , 'value' => 'Receipt', 'document_type' => 'thermal' ] ,
                [ 'label' => 'Shift / Day Report' , 'value' => 'Report', 'document_type' => 'thermal' ] ,
                
                // A4 Documents
                [ 'label' => 'Tax Invoice' , 'value' => 'Invoice', 'document_type' => 'a4' ] ,
                [ 'label' => 'Quotation / Estimate' , 'value' => 'Quotation', 'document_type' => 'a4' ] ,
                [ 'label' => 'Stock Transfer Slip' , 'value' => 'Transfer', 'document_type' => 'a4' ] ,
                
                // Labels
                [ 'label' => 'Product Barcode Labels' , 'value' => 'Labels', 'document_type' => 'label' ] ,
            ] );
        }
    }
}