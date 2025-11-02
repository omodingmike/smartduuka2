<?php

    namespace App\Console\Commands;

    use App\Models\Stock;
    use App\Models\User;
    use App\Services\CommissionCalculator;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\DB;

    class CalculateCommissions extends Command
    {
        protected $signature   = 'commissions:calculate';
        protected $description = 'Calculate total commissions for each user based on sold stock';

        public function handle(CommissionCalculator $calculator)
        {

            DB::transaction( function () use ($calculator) {
                $stocks = Stock::with( [ 'user' , 'product.variations' ] )
                               ->where( 'sold' , '>' , 0 )
                               ->get();
                $userTotals = [];
                foreach ( $stocks as $stock ) {
                    $commission = $calculator->calculateForStock( $stock );
                    $userId = $stock->user_id;
                    if ( ! isset( $userTotals[ $userId ] ) ) {
                        $userTotals[ $userId ] = 0;
                    }
                    $userTotals[ $userId ] += $commission;
                }

                foreach ( $userTotals as $userId => $totalCommission ) {
                    User::where( 'id' , $userId )->update( [ 'commission' => $totalCommission ] );
                }
            } );

        }
    }
