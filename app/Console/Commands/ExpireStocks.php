<?php

    namespace App\Console\Commands;

    use App\Enums\StockStatus;
    use App\Models\Damage;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\Stock;
    use Illuminate\Console\Command;
    use Stancl\Tenancy\Concerns\HasATenantsOption;
    use Stancl\Tenancy\Concerns\TenantAwareCommand;

    class ExpireStocks extends Command
    {
        use TenantAwareCommand , HasATenantsOption;

        protected $signature = 'stocks:expire';

        protected $description = 'Move expired stock into damage records and negate their quantities';

        public function handle() : void
        {
//        Tenant::all()->runForEach(function (Tenant $tenant) {
            $this->expireStocksForTenant();
//        });
        }

        private function expireStocksForTenant() : void
        {
            Stock::query()
                 ->whereNotNull( 'expiry_date' )
                 ->where( 'expiry_date' , '<' , now()->endOfDay() )
                 ->where( 'quantity' , '>' , 0 )
                 ->chunkById( 100 , function ($stocks) {
                     foreach ( $stocks as $stock ) {
                         $this->processExpiredStock( $stock );
                     }
                 } );
        }

        private function processExpiredStock(Stock $stock) : void
        {
            $stock->quantity = -abs( $stock->quantity );
            $stock->save();

            $damage = Damage::create( [
                'date'         => now() ,
                'reference_no' => 'D' . time() ,
                'subtotal'     => $stock->subtotal ,
                'tax'          => $stock->tax ,
                'discount'     => $stock->discount ,
                'total'        => $stock->total ,
                'note'         => 'Stock Expired' ,
            ] );

            if ( $stock->products->isEmpty() ) {
                return;
            }

            foreach ( $stock->products as $product ) {
                $this->createDamageStockEntry( $damage , $stock , $product );
            }
        }

        private function createDamageStockEntry(Damage $damage , Stock $stock , Product $product) : void
        {
            Stock::create( [
                'model_type'      => Damage::class ,
                'model_id'        => $damage->id ,
                'reference'       => 'D' . time() ,
                'item_type'       => $product->variations->isNotEmpty()
                    ? ProductVariation::class
                    : Product::class ,
                'product_id'      => $product->id ,
                'variation_names' => $product->variation_names ?? NULL ,
                'item_id'         => $product->id ,
                'price'           => $product->buying_price ,
                'quantity'        => -$stock->quantity ,
                'discount'        => $stock->discount ,
                'tax'             => $stock->tax ,
                'subtotal'        => $stock->subtotal ,
                'total'           => $stock->total ,
                'sku'             => $product->sku ,
                'status'          => StockStatus::EXPIRED ,
            ] );
        }
    }
