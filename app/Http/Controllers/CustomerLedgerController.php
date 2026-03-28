<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CustomerLedgerRequest;
    use App\Http\Resources\CustomerLedgerResource;
    use App\Models\CustomerLedger;

    class CustomerLedgerController extends Controller
    {
        public function index()
        {
            return CustomerLedgerResource::collection( CustomerLedger::all() );
        }

        public function store(CustomerLedgerRequest $request)
        {
            return new CustomerLedgerResource( CustomerLedger::create( $request->validated() ) );
        }

        public function show(CustomerLedger $customerLedger)
        {
            return new CustomerLedgerResource( $customerLedger );
        }

        public function update(CustomerLedgerRequest $request , CustomerLedger $customerLedger)
        {
            $customerLedger->update( $request->validated() );

            return new CustomerLedgerResource( $customerLedger );
        }

        public function destroy(CustomerLedger $customerLedger)
        {
            $customerLedger->delete();

            return response()->json();
        }
    }
