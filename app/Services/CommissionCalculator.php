<?php

    namespace App\Services;

    use App\Enums\CommissionType;
    use App\Models\Commission;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\Stock;

    class CommissionCalculator
    {
        public function calculateForStock(Stock $stock) : float
        {
            $user      = $stock->distributor;
            $variation = NULL;
            $item      = $stock->item;
            $price     = match ( TRUE ) {
                $item instanceof Product          => $item->selling_price ,
                $item instanceof ProductVariation => $item->price ,
                default                           => 0 ,
            };

            if ( $item instanceof ProductVariation ) {
                $variation = ProductVariation::with( 'product' )->find( $item->id );
                $product   = $variation?->product;
            }
            else {
                $product = Product::find( $item->id );
            }
            $commission = Commission::getApplicableCommission( $user , $product , $variation );

            if ( ! $commission ) {
                return 0.0;
            }

            $baseAmount = $stock->sold * $price;

            if ( $commission->commission_type == CommissionType::PERCENTAGE ) {
                return ( $baseAmount * $commission->commission_value ) / 100;
            }
            return $stock->sold * $commission->commission_value;
        }

        public function calculateForPosStock(Stock $stock) : float
        {
            $variation = NULL;
            $item      = $stock->item;

            if ( $item instanceof ProductVariation ) {
                $variation = ProductVariation::find( $item->id );
                $product   = $variation?->product;
            }
            else {
                $product = Product::find( $item->id );
            }
            $commission = Commission::getApplicableCommission( $stock->user , $product , $variation );

            if ( ! $commission ) {
                return 0.0;
            }

            $total    = $stock->total;
            $quantity = $stock->purchase_quantity;

            if ( $commission->commission_type == CommissionType::PERCENTAGE ) {
                return $total * ( $commission->commission_value / 100 );
            }
            return $quantity * $commission->commission_value;
        }

        public function calculateTotalSales(Stock $stock) : float
        {
            $item  = $stock->item;
            $price = match ( TRUE ) {
                $item instanceof Product          => $item->selling_price ,
                $item instanceof ProductVariation => $item->price ,
                default                           => 0 ,
            };
            return $stock->sold * $price;
        }
    }
