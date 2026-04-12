<?php

namespace App\Http\Controllers\Cashflow;

use App\Http\Requests\Cashflow\CurrencyRequest;
use App\Http\Resources\Cashflow\CurrencyResource;
use App\Models\Currency;
use App\Traits\HasAdvancedFilter;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    use HasAdvancedFilter;

    public function index(Request $request)
    {
        return CurrencyResource::collection($this->filter(new Currency(), $request));
    }

    public function store(CurrencyRequest $request)
    {
        $currency = Currency::create($request->validated());
        activityLog("Created Currency {$currency->name}", $request->header('X-App-Id'), $currency);

        return response()->json();
    }

    public function update(CurrencyRequest $request, Currency $currency)
    {
        $currency->update($request->validated());
        activityLog("Updated Currency {$currency->name}", $request->header('X-App-Id'), $currency);
        return response()->json();
    }

    public function destroy(Request $request)
    {
        $ids = $request->input('ids', []);
        foreach ($ids as $id) {
            $currency = Currency::find($id);
            activityLog("Deleted Currency {$currency->name}", $request->header('X-App-Id'), $currency);
            $currency->delete();
        }

        return response()->json();
    }
}
