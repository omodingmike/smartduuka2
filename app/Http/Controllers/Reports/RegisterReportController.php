<?php

    namespace App\Http\Controllers\Reports;

    use App\Enums\RegisterStatus;
    use App\Http\Resources\RegisterResource;
    use App\Models\Register;
    use Illuminate\Http\Request;

    class RegisterReportController
    {
        public function index(Request $request)
        {
            $query  = Register::with( [
                'user' ,
                'orders.orderProducts.item' ,
                'posPayments.paymentMethod' ,
                'expensesPayments.expense' ,
                'walletTransactions' ,
            ] );

            if ( $request->filled( 'status' ) && $request->status !== 'all' ) {
                $status = $request->status === 'open'
                    ? RegisterStatus::OPEN
                    : RegisterStatus::CLOSED;

                $query->where( 'status' , $status );
            }

            if ( $request->filled( 'start' ) && $request->filled( 'end' ) ) {
                $query->whereBetween( 'created_at' , [
                    $request->start . ' 00:00:00' ,
                    $request->end . ' 23:59:59'
                ] );
            }

            if ( $request->filled( 'query' ) ) {
                $searchTerm = $request->input( 'query' );

                $query->where( function ($q) use ($searchTerm) {
                    $numericId = ltrim( str_replace( 'REG-' , '' , strtoupper( $searchTerm ) ) , '0' );

                    if ( is_numeric( $numericId ) ) {
                        $q->where( 'id' , $numericId );
                    }

                    $q->orWhereHas( 'user' , function ($userQuery) use ($searchTerm) {
                        $userQuery->where( 'name' , 'ilike' , "%{$searchTerm}%" );
                    } );
                } );
            }

            if ( $request->boolean( 'unpaginated' ) ) {
                $registers = $query->latest()->get();
                return RegisterResource::collection( $registers );
            }

            $registers = $query->latest()->paginate( $request->input( 'per_page' , 15 ) );

            return RegisterResource::collection( $registers );
        }
    }