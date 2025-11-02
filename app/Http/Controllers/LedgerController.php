<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreLedgerRequest;
    use App\Http\Requests\UpdateLedgerRequest;
    use App\Http\Resources\LedgerResource;
    use App\Http\Resources\LedgerTransactionResource;
    use App\Models\Ledger;

    class LedgerController extends Controller
    {
        public function index()
        {
            //
        }

        public function store(StoreLedgerRequest $request)
        {
            Ledger::create($request->validated());
        }

        public function show(Ledger $ledger)
        {
            return new LedgerResource($ledger);
        }

        public function transactions(Ledger $ledger)
        {
            return LedgerTransactionResource::collection($ledger->transactions);
        }

        public function update(UpdateLedgerRequest $request , Ledger $ledger)
        {
            $ledger->update($request->validated());
        }

        public function destroy(Ledger $ledger)
        {
            $ledger->delete();
        }
    }
