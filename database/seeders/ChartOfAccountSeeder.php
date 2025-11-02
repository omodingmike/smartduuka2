<?php

    namespace Database\Seeders;

    use App\Models\ChartOfAccountGroup;
    use Illuminate\Database\Seeder;

    class ChartOfAccountSeeder extends Seeder
    {
        /**
         * Run the database seeds.
         */
        public function run() : void
        {
            $coas = [
                [
                    'name'      => 'Assets' ,
                    'parent_id' => null ,
                ] ,
                [
                    'name'      => 'Current Assets' ,
                    'parent_id' => 1 ,
                ] ,
                [
                    'name'      => 'Fixed Assets' ,
                    'parent_id' => 1 ,
                ] ,
                [
                    'name'      => 'Liabilities' ,
                    'parent_id' => null ,
                ] ,
                [
                    'name'      => 'Current Liabilities' ,
                    'parent_id' => 4 ,
                ] ,
                [
                    'name'      => 'Long Term Liabilities' ,
                    'parent_id' => 4 ,
                ] ,
                [
                    'name'      => 'Owner\'s Equity' ,
                    'parent_id' => null ,
                ] ,
                [
                    'name'      => 'Revenue' ,
                    'parent_id' => null ,
                ] ,
                [
                    'name'      => 'Expenses' ,
                    'parent_id' => null ,
                ] ,
            ];
            foreach ( $coas as $coa ) {
                ChartOfAccountGroup::create($coa);
            }
        }
    }
