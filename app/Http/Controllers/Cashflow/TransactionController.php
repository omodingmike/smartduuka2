<?php

namespace App\Http\Controllers\Cashflow;

use App\Enums\CashType;
use App\Http\Requests\Cashflow\TransactionRequest;
use App\Http\Resources\Cashflow\TransactionResource;
use App\Models\Cashflow\MotherAccount;
use App\Models\Cashflow\SubAccount;
use App\Models\Cashflow\Transaction;
use App\Traits\HasAdvancedFilter;
use App\Traits\SaveMedia;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    use SaveMedia, HasAdvancedFilter;

    public function index(Request $request)
    {
        return TransactionResource::collection($this->filter(Transaction::with(['entity', 'currency', 'transactionCategory', 'accountable']), $request));
    }

    public function store(TransactionRequest $request)
    {
        try {
            $data = $request->validated();
            [$type, $id] = explode('-', $data['account_id'], 2);

            $accountableType = match ($type) {
                'mother' => MotherAccount::class,
                'sub' => SubAccount::class,
                default => throw new \InvalidArgumentException("Invalid account type: $type"),
            };
            $is_cash_in = (int) $data['cash_type'] == CashType::CASH_IN->value;
            $is_cash_out = (int) $data['cash_type'] == CashType::CASH_OUT->value;
            $amount = $data['amount'] * $data['exchange_rate'];
            if (! isset($data['fee'])) {
                $data['fee'] = 0;
            }
            if (! isset($data['description'])) {
                $data['description'] = '';
            }
            $fee = $data['fee'] * $data['exchange_rate'];
            $data['amount'] = $is_cash_out ? $amount + $fee : $amount;
            $data['fee'] = $fee;
            $transaction = Transaction::create($data + [
                    'reference' => 'reference',
                    'accountable_id' => $id,
                    'accountable_type' => $accountableType,
                    'cash_in' => $is_cash_in ? $amount : 0,
                    'cash_out' => $is_cash_out ? $amount + $data['fee'] : 0,
                ]);
            $account = $accountableType::find($id);

            if ($is_cash_in) {
                $account->increment('cash_in', $amount);
            }
            if ($is_cash_out) {
                $account->increment('cash_out', $amount + $data['fee']);
            }

            $this->saveMedia($request, $transaction);
            $transaction->update(['reference' => recordId('TX', $transaction)]);
            activityLog("Created Transaction {$transaction->reference}", $request->header('X-App-Id'), $transaction);

            return new TransactionResource($transaction->load(['entity', 'currency', 'transactionCategory', 'accountable']));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function show(Transaction $transaction)
    {
        return new TransactionResource($transaction->load(['entity', 'currency', 'transactionCategory', 'accountable']));
    }

    public function update(TransactionRequest $request, Transaction $transaction)
    {
        try {
            $data = $request->validated();

            [$type, $id] = explode('-', $data['account_id'], 2);

            $is_cash_in = (int) $data['cash_type'] == CashType::CASH_IN->value;
            $is_cash_out = (int) $data['cash_type'] == CashType::CASH_OUT->value;

            $accountableType = match ($type) {
                'mother' => MotherAccount::class,
                'sub' => SubAccount::class,
                default => throw new \InvalidArgumentException("Invalid account type: $type"),
            };
            $account = $accountableType::find($id);

            $amount = $data['amount'] * $data['exchange_rate'];
            if (! isset($data['fee'])) {
                $data['fee'] = 0;
            }
            $fee = $data['fee'] * $data['exchange_rate'];
            $data['amount'] = $is_cash_out ? $amount + $fee : $amount;
            $data['fee'] = $fee;

            if ($transaction->cash_in > 0) {
                $account->decrement('cash_in', $transaction->cash_in);
            }
            if ($transaction->cash_out > 0) {
                $account->decrement('cash_out', $transaction->cash_out);
            }

            $transaction->update($data + [
                    'accountable_id' => $id,
                    'accountable_type' => $accountableType,
                    'cash_in' => $is_cash_in ? $amount : 0,
                    'cash_out' => $is_cash_out ? $amount + $data['fee'] : 0,
                ]);
            if ($is_cash_in) {
                $account->increment('cash_in', $amount);
            }
            if ($is_cash_out) {
                $account->increment('cash_out', $amount + $data['fee']);
            }

            activityLog("Updated Transaction {$transaction->reference}", $request->header('X-App-Id'), $transaction);

            return new TransactionResource($transaction->load(['entity', 'currency', 'transactionCategory', 'accountable']));
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    //        public function update(TransactionRequest $request , Transaction $transaction)
    //        {
    //            try {
    //                $transaction->update( $request->validated() );
    //                activityLog( "Updated Transaction {$transaction->reference}" , $transaction );
    //                return new TransactionResource( $transaction->load( [ 'entity' , 'currency' , 'transactionCategory' , 'accountable' ] ) );
    //            } catch ( \Exception $e ) {
    //                throw new \Exception( $e->getMessage() );
    //            }
    //        }

    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            foreach ($ids as $id) {
                $transaction = Transaction::find($id);
                activityLog("Deleted Transaction {$transaction->reference}", $request->header('X-App-Id'), $transaction);
                $transaction->delete();
            }
            return response()->json();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
