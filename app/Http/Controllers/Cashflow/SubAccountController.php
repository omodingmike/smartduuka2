<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Requests\Cashflow\SubAccountRequest;
use App\Http\Resources\Cashflow\SubAccountResource;
use App\Models\Cashflow\MotherAccount;
use App\Models\Cashflow\SubAccount;
use App\Traits\HasAdvancedFilter;
use Illuminate\Http\Request;

class SubAccountController extends Controller
{
    use HasAdvancedFilter;

    public function index(Request $request)
    {
        $subAccounts = $this->filter(new SubAccount, $request);
        $total_cash_in = SubAccount::sum('cash_in') + MotherAccount::sum('cash_in');
        $total_cash_out = SubAccount::sum('cash_out') + MotherAccount::sum('cash_out');
        $net = $total_cash_in - $total_cash_out;

        return SubAccountResource::collection($subAccounts)->additional([
            'meta' => [
                'total_cash_in' => $total_cash_in,
                'total_cash_in_currency' => currency($total_cash_in),
                'total_cash_out' => $total_cash_out,
                'total_cash_out_currency' => currency($total_cash_out),
                'net' => $net,
                'net_currency' => currency($net),
            ],
        ]);
    }

    public function store(SubAccountRequest $request)
    {
        try {
            $subAccount = SubAccount::create($request->validated() + ['mother_account_id' => MotherAccount::first()->id]);
            activityLog("Created Sub Account {$subAccount->name}", $request->header('X-App-Id'), $subAccount);
            return response()->json();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function update(SubAccountRequest $request, SubAccount $subAccount)
    {
        try {
            $subAccount->update($request->validated());
            activityLog("Updated Sub Account {$subAccount->name}", $request->header('X-App-Id'), $subAccount);
            return new SubAccountResource($subAccount);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    public function destroy(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            foreach ($ids as $id) {
                $subAccount = SubAccount::find($id);
                activityLog("Deleted Sub Account {$subAccount->name}", $request->header('X-App-Id'), $subAccount);
                $subAccount->delete();
            }
            return response()->json();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
