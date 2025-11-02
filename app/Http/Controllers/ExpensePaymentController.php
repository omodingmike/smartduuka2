<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\ExpensePayment;
use App\Traits\ApiResponse;
use App\Traits\AuthUser;
use App\Traits\FilesTrait;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpensePaymentController extends Controller
{
    use  ApiResponse, FilesTrait, AuthUser;

    public function index()
    {
        return $this->response(success: true, message: 'success', data: ExpensePayment::where('expense_id', request()->expense_id)->get());
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount'        => 'required',
            'date'          => 'required',
            'paymentMethod' => 'required|not_in:null',
            'expense_id'    => 'required',
            'referenceNo'   => 'required_if:paymentMethod,2|required_if:paymentMethod,3|required_if:paymentMethod,4',
        ]);
        $validator->setAttributeNames([
            'expense_id'    => 'expense_id',
            'amount'        => 'amount',
            'date'          => 'date',
            'paymentMethod' => 'paymentMethod',
            'referenceNo'   => 'referenceNo',
        ]);
        if ($validator->fails()) {
            return $this->response(message: $validator->errors()->first(), data: [$validator->errors()->keys()[0] => $validator->errors()->first()]);
        }
        try {
            DB::transaction(function () use ($request, $validator) {
                $expense = Expense::find($request->expense_id);
                $expense->increment('paid', $request->amount);
                $this->saveFiles($request, $expense, ['file' => 'attachment']);
                $validated = $validator->validated();
                $validated ['date'] = date('Y-m-d H:i:s', strtotime($request->date));
                $validated ['user_id'] = $this->id();
                ExpensePayment::create($validated);
            });
            return $this->response(success: true, message: 'success');
        } catch (Exception $e) {
            return $this->response(message: $e->getMessage());
        }
    }

    public function show(ExpensePayment $expensePayment)
    {
        //
    }

    public function update(Request $request, ExpensePayment $expensePayment)
    {
        //
    }

    public function destroy(ExpensePayment $expensePayment)
    {
        //
    }
}
