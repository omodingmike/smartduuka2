<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(CompanyTableSeeder::class);
        $this->call(ThemeTableSeeder::class);
        $this->call(SiteTableSeeder::class);
        $this->call(MenuTableSeeder::class);
        $this->call(PermissionTableSeeder::class);
        $this->call(RoleTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(RolePermissionTableSeeder::class);
        $this->call(LanguageTableSeeder::class);
        $this->call(BarcodeTableSeeder::class);
        $this->call(MenuTemplateTableSeeder::class);
        $this->call(MenuSectionTableSeeder::class);
//        $this->call(MailTableSeeder::class);
        $this->call(ProductBrandTableSeeder::class);
//        $this->call(ProductCategoryTableSeeder::class);
//        $this->call(TaxTableSeeder::class);
//        $this->call(UnitTableSeeder::class);
//        $this->call(ProductAttributeTableSeeder::class);
//        $this->call(ProductAttributeOptionTableSeeder::class);
        $this->call(SupplierTableSeeder::class);
//        $this->call(ProductTableSeeder::class);
//        $this->call(ProductVariationTableSeeder::class);
//        $this->call(PurchaseTableSeeder::class);
//        $this->call(PosOrderTableSeeder::class);
//        $this->call(DamageTableSeeder::class);
//        $this->call(ProductReviewTableSeeder::class);
//        $this->call(CurrencyTableSeeder::class);

        Artisan::call( 'l:seed');
    }
}
