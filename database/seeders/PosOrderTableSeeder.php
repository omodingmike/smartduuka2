<?php

namespace Database\Seeders;


use App\Enums\Ask;
use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Enums\Source;
use App\Enums\Status;
use App\Models\ProductVariation;
use App\Models\Stock;
use App\Models\StockTax;
use Dipokhalder\EnvEditor\EnvEditor;
use Illuminate\Database\Seeder;
use App\Models\Order;

class PosOrderTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $envService = new EnvEditor();
        if ($envService->getValue('DEMO') && $envService->getValue('DISPLAY') == 'fashion') {
            Order::insert([
                [
                    'order_serial_no' => date('dmy') . '4',
                    'user_id'         => 2,
                    'subtotal'        => 160.000000,
                    'tax'             => 17.000000,
                    'discount'        => 8.000000,
                    'shipping_charge' => 0.000000,
                    'total'           => 169.000000,
                    'order_type'      => OrderType::POS,
                    'order_datetime'  => now(),
                    'payment_method'  => 1,
                    'payment_status'  => PaymentStatus::PAID,
                    'status'          => OrderStatus::DELIVERED,
                    'active'          => Ask::YES,
                    'source'          => Source::POS,
                    'created_at'      => now(),
                    'updated_at'      => now()
                ]
            ]);

            Stock::insert([
                [
                    'product_id'      => 3,
                    'model_type'      => Order::class,
                    'model_id'        => 1,
                    'item_type'       => ProductVariation::class,
                    'product_id'         => 19,
                    'variation_names' => 'White | M',
                    'sku'             => '59622955',
                    'price'           => 60.000000,
                    'quantity'        => -1,
                    'discount'        => 0.000000,
                    'tax'             => 12.000000,
                    'subtotal'        => 60.000000,
                    'total'           => 72.000000,
                    'status'          => Status::ACTIVE,
                    'created_at'      => now(),
                    'updated_at'      => now()
                ],
                [
                    'product_id'      => 13,
                    'model_type'      => Order::class,
                    'model_id'        => 1,
                    'item_type'       => ProductVariation::class,
                    'product_id'         => 103,
                    'variation_names' => 'Black | M',
                    'sku'             => '14143859',
                    'price'           => 100.000000,
                    'quantity'        => -1,
                    'discount'        => 0.000000,
                    'tax'             => 5.800000,
                    'subtotal'        => 100.000000,
                    'total'           => 105.800000,
                    'status'          => Status::ACTIVE,
                    'created_at'      => now(),
                    'updated_at'      => now()
                ],
            ]);

            StockTax::insert([
                [
                    'stock_id'   => 619,
                    'product_id' => 3,
                    'tax_id'     => 5,
                    'name'       => 'VAT-20',
                    'code'       => 'VAT-20%',
                    'tax_rate'   => 20.000000,
                    'tax_amount' => 12.000000,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
                [
                    'stock_id'   => 620,
                    'product_id' => 13,
                    'tax_id'     => 20,
                    'name'       => 'VAT-5',
                    'code'       => 'VAT-5%',
                    'tax_rate'   => 5.000000,
                    'tax_amount' => 5.000000,
                    'created_at' => now(),
                    'updated_at' => now()
                ],
            ]);
        }
    }
}
