<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLedgerTransactionRequest;
use App\Http\Requests\UpdateLedgerTransactionRequest;
use App\Models\LedgerTransaction;

class LedgerTransactionController extends Controller
{
    public function index()
    {
        //
    }

    public function store(StoreLedgerTransactionRequest $request)
    {
        //
    }

    public function show(LedgerTransaction $ledgerTransaction)
    {
        //
    }

    public function update(UpdateLedgerTransactionRequest $request, LedgerTransaction $ledgerTransaction)
    {
        //
    }

    public function destroy(LedgerTransaction $ledgerTransaction)
    {
        //
    }
}
