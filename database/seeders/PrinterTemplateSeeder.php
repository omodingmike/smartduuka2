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
                    [ 'label' => 'Sales Receipt' , 'value' => 'Receipt' ] ,
                    [ 'label' => 'Tax Invoice' , 'value' => 'Invoice' ] ,
                    [ 'label' => 'Quotation / Estimate' , 'value' => 'Quotation' ] ,
                    [ 'label' => 'Product Barcode Labels' , 'value' => 'Labels' ] ,
                    [ 'label' => 'Stock Transfer Slip' , 'value' => 'Transfer' ] ,
                    [ 'label' => 'Shift / Day Report' , 'value' => 'Report' ] ,
                ] );
            }
        }
    }
