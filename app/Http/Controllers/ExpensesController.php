<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\ExpenseResouce;
    use App\Models\Expense;
    use App\Traits\ApiResponse;
    use App\Traits\AuthUser;
    use App\Traits\FilesTrait;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Validator;

    class ExpensesController extends Controller
    {
        use ApiResponse , FilesTrait , AuthUser;

        public function index(PaginateRequest $request)
        {
            try {
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_type') ?? 'desc';
                $name        = $request->name;
                $from_date   = $request->from_date;
                $to_date     = $request->to_date;

                $data = Expense::with('expenseCategory')->when($name , function ($query) use ($name) {
                    $query->where('name' , 'like' , '%' . $name . '%');
                })->when($from_date && $to_date , function ($query) use ($from_date , $to_date) {
                    $query->whereBetween('created_at' , [ Carbon::parse($from_date)->copy()->startOfDay() , Carbon::parse($to_date)->copy()->endOfDay() ]);
                })->orderBy($orderColumn , $orderType)->$method($methodValue);

                return ExpenseResouce::collection($data);
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function store(Request $request)
        {
            $validator = Validator::make(
                $request->all() ,
                [
                    'name'          => 'required' ,
                    'amount'        => 'required' ,
                    'date'          => 'required' ,
                    'category'      => 'required' ,
                    'paymentMethod' => 'required' ,
                    'referenceNo'   => 'required_if:paymentMethod,2|required_if:paymentMethod,3|required_if:paymentMethod,4' ,
                    'recurs'        => 'required_if:isRecurring,true' ,
                    'repeatsOn'     => 'required_if:isRecurring,true' ,
                    'paymentAmount' => 'sometimes|integer' ,
                    'paidOn'        => 'sometimes|date' ,
                ]
            );
            $validator->setAttributeNames([
                'name'          => 'name' ,
                'amount'        => 'amount' ,
                'date'          => 'date' ,
                'note'          => 'expense note' ,
                'category'      => 'category' ,
                'paymentMethod' => 'paymentMethod' ,
                'referenceNo'   => 'referenceNo' ,
                'recurs'        => 'recurs' ,
                'repeatsOn'     => 'repeatsOn' ,
                'paymentAmount' => 'paymentAmount' ,
                'paidOn'        => 'paidOn' ,
            ]);
            if ( $validator->fails() ) {
                return $this->response(message: $validator->errors()->first() , data: [ $validator->errors()->keys()[0] => $validator->errors()->first() ]);
            }
            try {
                DB::beginTransaction();
                $format  = 'Y-m-d H:i:s';
                $expense = Expense::create([
                    'name'          => $request->name ,
                    'amount'        => $request->amount ,
                    'date'          => date($format , strtotime($request->date)) ,
                    'category'      => $request->category ,
                    'paymentMethod' => $request->paymentMethod ,
                    'note'          => $request->note ?? "" ,
                    'referenceNo'   => $request->referenceNo ,
                    'isRecurring'   => $request->isRecurring ?? 0 ,
                    'recurs'        => $request->recurs ,
                    'user_id'       => $this->id() ,
                    'repetitions'   => $request->repetitions ?? 0 ,
                    'repeats_on'    => $request->repeatsOn ? date($format , strtotime($request->repeatsOn)) : null ,
                    'paid'          => $request->paymentAmount ?? 0 ,
                    'paid_on'       => $request->paidOn ? date($format , strtotime($request->paidOn)) : null ,
                ]);
                $this->saveFiles($request , $expense , [ 'attachment' => 'attachment' ]);
                if ( $expense ) {
                    DB::commit();
                    return $this->response(true , message: 'success' , data: $expense);
                } else {
                    DB::rollBack();
                    return $this->response(message: 'Expense creation failed');
                }
            } catch ( Exception $exception ) {
                info($exception->getMessage());
                DB::rollBack();
                return $this->response(message: $exception->getMessage());
            }
        }

        public function show(string $id)
        {
            $expense = Expense::with('expenseCategory')->find($id);
            return $this->response(true , 'success' , data: $expense);
        }

        public function update(Expense $expense , Request $request)
        {
            $validator = Validator::make(
                $request->all() ,
                [
                    'name'          => 'required' ,
                    'amount'        => 'required' ,
                    'date'          => 'required' ,
                    'category'      => 'required' ,
                    'paymentMethod' => 'required' ,
                    'referenceNo'   => 'required_if:paymentMethod,2|required_if:paymentMethod,3|required_if:paymentMethod,4' ,
                    'recurs'        => 'required_if:isRecurring,true' ,
                    'repeatsOn'     => 'required_if:isRecurring,true' ,
                    'paymentAmount' => 'sometimes|integer' ,
                    'paidOn'        => 'sometimes|date' ,
                ]
            );
            $validator->setAttributeNames([
                'name'          => 'name' ,
                'amount'        => 'amount' ,
                'date'          => 'date' ,
                'note'          => 'expense note' ,
                'category'      => 'category' ,
                'paymentMethod' => 'paymentMethod' ,
                'referenceNo'   => 'referenceNo' ,
                'recurs'        => 'recurs' ,
                'repeatsOn'     => 'repeatsOn' ,
                'paymentAmount' => 'paymentAmount' ,
                'paidOn'        => 'paidOn' ,
            ]);
            if ( $validator->fails() ) {
                return $this->response(message: $validator->errors()->first() , data: [ $validator->errors()->keys()[0] => $validator->errors()->first() ]);
            }
            return $expense->update($request->all());
        }

        public function destroy(string $id)
        {
            $deleted = Expense::find($id)->delete();
            if ( $deleted ) return $this->response(true , 'success');
            return $this->response(message: 'Expense deletion failed');
        }
    }
