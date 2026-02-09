<?php

    namespace App\Http\Controllers;

    use App\Enums\ExpenseType;
    use App\Http\Requests\ExpenseRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\ExpenseResouce;
    use App\Models\Expense;
    use App\Traits\ApiResponse;
    use App\Traits\AuthUser;
    use App\Traits\FilesTrait;
    use App\Traits\SaveMedia;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Log;

    class ExpensesController extends Controller
    {
        use ApiResponse , FilesTrait , AuthUser , SaveMedia;

        public function index(PaginateRequest $request)
        {
            try {
                $method      = $request->get( 'paginate' , 0 ) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get( 'paginate' , 0 ) == 1 ? $request->get( 'per_page' , 10 ) : '*';
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';
                $name        = $request->name;
                $from_date   = $request->from_date;
                $to_date     = $request->to_date;

                $data = Expense::with( 'expenseCategory' )->when( $name , function ($query) use ($name) {
                    $query->where( 'name' , 'like' , '%' . $name . '%' );
                } )->when( $from_date && $to_date , function ($query) use ($from_date , $to_date) {
                    $query->whereBetween( 'created_at' , [ Carbon::parse( $from_date )->copy()->startOfDay() , Carbon::parse( $to_date )->copy()->endOfDay() ] );
                } )->orderBy( $orderColumn , $orderType )->$method( $methodValue );

                return ExpenseResouce::collection( $data );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function store(ExpenseRequest $request)
        {
            try {
                $isRecurring = $request->integer( 'isRecurring' );
                $expense     = Expense::create( [
                    'name'                => $request->name ,
                    'amount'              => $request->amount ,
                    'date'                => $request->date ,
                    'expense_category_id' => $request->category ,
                    'note'                => $request->note ?? "" ,
                    'is_recurring'        => $request->isRecurring ?? 0 ,
                    'base_amount'         => $request->baseAmount ,
                    'expense_type'        => $isRecurring == 1 ? ExpenseType::RECURRING->value : ExpenseType::NON_RECURRING->value ,
                    'extra_charge'        => $request->extraCharge ,
                    'paid'                => $request->paidAmount ?? 0 ,
                    'register_id'         => register()->id ,
                ] );
                $this->saveMedia( $request , $expense );
                return $this->response( TRUE , message: 'success' , data: $expense );
            } catch ( Exception $exception ) {
                info( $exception->getMessage() );
                return $this->response( message: $exception->getMessage() );
            }
        }

        public function show(string $id)
        {
            $expense = Expense::with( 'expenseCategory' )->find( $id );
            return $this->response( TRUE , 'success' , data: $expense );
        }

        public function update(Expense $expense , ExpenseRequest $request)
        {
            try {
                $isRecurring = $request->integer( 'isRecurring' );
                $expense->update( [
                    'name'                => $request->name ,
                    'amount'              => $request->amount ,
                    'date'                => $request->date ,
                    'expense_category_id' => $request->category ,
                    'note'                => $request->note ?? "" ,
                    'is_recurring'        => $request->isRecurring ?? 0 ,
                    'base_amount'         => $request->baseAmount ,
                    'expense_type'        => $isRecurring == 1 ? ExpenseType::RECURRING->value : ExpenseType::NON_RECURRING->value ,
                    'extra_charge'        => $request->extraCharge ,
                    'paid'                => $request->paidAmount ?? 0 ,
                ] );
                $this->saveMedia( $request , $expense );
                return $this->response( TRUE , message: 'success' , data: $expense );
            } catch ( Exception $exception ) {
                info( $exception->getMessage() );
                return $this->response( message: $exception->getMessage() );
            }
        }

        public function destroy(Request $request)
        {
            try {
                DB::transaction( function () use ($request) {
                    foreach ( $request->ids as $id ) {
                        $expense = Expense::find( $id );
                        if ($expense) {
//                            if ($expense->register) {
//                                $expense->register->delete();
//                            }
                            $expense->delete();
                        }
                    }
                } );
                return $this->response( TRUE , 'success' );
            } catch ( \Throwable $e ) {
                return $this->response( message: $e->getMessage() );
            }
        }
    }
