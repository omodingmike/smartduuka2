<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CustomerWalletTransactionRequest;
    use App\Http\Resources\CustomerWalletTransactionResource;
    use App\Models\CustomerWalletTransaction;

    class CustomerWalletTransactionController extends Controller
    {
        public function index()
        {
            return CustomerWalletTransactionResource::collection( CustomerWalletTransaction::all() );
        }

        public function store(CustomerWalletTransactionRequest $request)
        {
            return new CustomerWalletTransactionResource( CustomerWalletTransaction::create( $request->validated() ) );
        }

        public function show(CustomerWalletTransaction $customerWalletTransaction)
        {
            return new CustomerWalletTransactionResource( $customerWalletTransaction );
        }

        public function update(CustomerWalletTransactionRequest $request , CustomerWalletTransaction $customerWalletTransaction)
        {
            $customerWalletTransaction->update( $request->validated() );

            return new CustomerWalletTransactionResource( $customerWalletTransaction );
        }

        public function destroy(CustomerWalletTransaction $customerWalletTransaction)
        {
            $customerWalletTransaction->delete();

            return response()->json();
        }
    }
