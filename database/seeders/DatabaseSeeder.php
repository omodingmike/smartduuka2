<?php

    namespace Database\Seeders;


    use Illuminate\Database\Seeder;

    class DatabaseSeeder extends Seeder
    {
        public function run() : void
        {
            $this->call( PrinterTemplateSeeder::class );
            $this->call( UserTableSeeder::class );
            $this->call( WarehouseSeeder::class );
            $this->call( LocationSeed::class );
//            $this->call( RoleTableSeeder::class );
//        $this->call(CompanyTableSeeder::class);
//        $this->call(ThemeTableSeeder::class);
//        $this->call(SiteTableSeeder::class);
//        $this->call(MenuTableSeeder::class);


//            DB::statement( 'TRUNCATE TABLE roles, permissions, users RESTART IDENTITY CASCADE' );
//            $this->call( PermissionTableSeeder::class );
//            $this->call( UserTableSeeder::class );
//            $this->call( PaymentMethodSeeder::class );

//        $this->call(RolePermissionTableSeeder::class);
//
//        $this->call(LanguageTableSeeder::class);
//        $this->call(BarcodeTableSeeder::class);
//        $this->call(MenuTemplateTableSeeder::class);
//        $this->call(MenuSectionTableSeeder::class);


//        $this->call(MailTableSeeder::class);
//        $this->call(ProductBrandTableSeeder::class);
//        $this->call(ProductCategoryTableSeeder::class);
//        $this->call(TaxTableSeeder::class);
//        $this->call(UnitTableSeeder::class);
//        $this->call(ProductAttributeTableSeeder::class);
//        $this->call(ProductAttributeOptionTableSeeder::class);
//        $this->call(SupplierTableSeeder::class);
//        $this->call(ProductTableSeeder::class);
//        $this->call(ProductVariationTableSeeder::class);
//        $this->call(PurchaseTableSeeder::class);
//        $this->call(PosOrderTableSeeder::class);
//        $this->call(DamageTableSeeder::class);
//        $this->call(ProductReviewTableSeeder::class);
//        $this->call(CurrencyTableSeeder::class);


        }
    }
