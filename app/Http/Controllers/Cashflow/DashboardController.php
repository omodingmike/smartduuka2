<?php

    namespace App\Http\Controllers\Cashflow;

    use App\Enums\TransactionStatus;
    use App\Http\Resources\Cashflow\EntityResource;
    use App\Http\Resources\Cashflow\TransactionResource;
    use App\Models\Cashflow\Entity;
    use App\Models\Cashflow\MotherAccount;
    use App\Models\Cashflow\SubAccount;
    use App\Models\Cashflow\Transaction;
    use Illuminate\Http\JsonResponse;
    use Smartisan\Settings\Facades\Settings;

    class DashboardController extends Controller
    {
        public function index() : JsonResponse
        {
            $transactions_query = Transaction::where( 'status' , TransactionStatus::CLEARED );
            $total_cash_in      = $transactions_query->sum( 'cash_in' );
            $total_cash_out     = $transactions_query->sum( 'cash_out' );
            $total_pending      = Transaction::where( 'status' , '<>' , TransactionStatus::CLEARED )->sum( 'amount' );
            $net                = $total_cash_in - $total_cash_out;

            $recentTransactions = Transaction::with( ['entity','accountable','transactionCategory'])->latest()->take( 5 )->get();

            $entities = Entity::all();

            $motherAccounts = MotherAccount::all( [ 'id' , 'name' ] )->map( function ($account) {
                return [ 'id' => 'mother-' . $account->id , 'name' => $account->name ];
            } );
            $subAccounts    = SubAccount::all( [ 'id' , 'name' ] )->map( function ($account) {
                return [ 'id' => 'sub-' . $account->id , 'name' => $account->name ];
            } );
            $accounts       = $motherAccounts->concat( $subAccounts );

            $settings = Settings::all();

            return response()->json( [
                'summary'            => [
                    'balance'           => $net ,
                    'balance_currency'  => currency( $net ) ,
                    'cash_in'           => $total_cash_in ,
                    'cash_in_currency'  => currency( $total_cash_in ) ,
                    'cash_out'          => $total_cash_out ,
                    'cash_out_currency' => currency( $total_cash_out ) ,
                    'pending'           => $total_pending ,
                    'pending_currency'  => currency( $total_pending ) ,
                ] ,
                'recentTransactions' => TransactionResource::collection( $recentTransactions ) ,
                'entities'           => EntityResource::collection( $entities ) ,
                'accounts'           => $accounts ,
                'settings'           => $settings ,
                'transactions'       => TransactionResource::collection( $recentTransactions ) ,
            ] );
        }
    }
