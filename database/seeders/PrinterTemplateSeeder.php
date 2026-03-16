<?php

    namespace Database\Seeders;

    use Illuminate\Database\Seeder;
    use Illuminate\Support\Facades\DB;

    class PrinterTemplateSeeder extends Seeder
    {
        public function run() : void
        {
            $templates = [
                // Thermal Documents
                [ 'label' => 'Sales Receipt' , 'value' => 'Receipt' , 'document_type' => 'thermal' ] ,
                [ 'label' => 'Shift / Day Report' , 'value' => 'Report' , 'document_type' => 'thermal' ] ,

                // A4 Documents
                [ 'label' => 'Tax Invoice' , 'value' => 'Invoice' , 'document_type' => 'a4' ] ,
                [ 'label' => 'Quotation / Estimate' , 'value' => 'Quotation' , 'document_type' => 'a4' ] ,
                [ 'label' => 'Stock Transfer Slip' , 'value' => 'Transfer' , 'document_type' => 'a4' ] ,

                // Labels
                [ 'label' => 'Product Barcode Labels' , 'value' => 'Labels' , 'document_type' => 'label' ] ,
            ];

            $definedTemplateValues = array_column( $templates , 'value' );

            $existingTemplateValues = DB::table( 'printer_templates' )->pluck( 'value' )->toArray();

            $missingValues = array_diff( $definedTemplateValues , $existingTemplateValues );

            if ( empty( $missingValues ) ) {
                return;
            }

            $templatesToInsert = [];
            foreach ( $templates as $template ) {
                if ( in_array( $template[ 'value' ] , $missingValues ) ) {
                    $templatesToInsert[] = $template;
                }
            }

            if ( ! empty( $templatesToInsert ) ) {
                DB::table( 'printer_templates' )->insert( $templatesToInsert );
            }
        }
    }