<?php

    namespace App\Traits;

    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    trait TableSchemaTrait
    {
        function dropColumnIfExists ( $table , $column ) : void
        {
            if ( Schema ::hasColumn( $table , $column ) ) {
                Schema ::table( $table , function ( Blueprint $table ) use ( $column ) {
                    $table -> dropColumn( $column );
                } );
            }
        }
    }