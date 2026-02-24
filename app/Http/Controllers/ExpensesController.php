<?php

    namespace App\Http\Controllers;

    use App\Enums\ExpenseType;
    use App\Http\Requests\ExpenseRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\ExpenseResource;
    use App\Http\Resources\ExpenseTitleResource;
    use App\Libraries\AppLibrary;
    use App\Models\Expense;
    use App\Models\ExpensePayment;
    use App\Models\ExpenseTitle;
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

        public function index(Request $request)
        {
            try {
                $orderColumn = $request->get( 'order_column' ) ?? 'id';
                $orderType   = $request->get( 'order_type' ) ?? 'desc';
                $from_date   = $request->start;
                $to_date     = $request->end;
                $category    = $request->category;
                $query       = $request->input( 'query' );
                $page        = $request->get( 'page' ) ?? 1;
                $perPage     = $request->get( 'perPage' ) ?? 10;

                $data = Expense::with( [ 'expenseCategory' , 'payments' ] )
                               ->when( $from_date && ! $to_date , function ($query) use ($from_date) {
                                   $query->whereDate( 'created_at' , '>=' , Carbon::parse( $from_date )->copy()->startOfDay() );
                               } )
                               ->when( $from_date && $to_date , function ($query) use ($from_date , $to_date) {
                                   $query->whereBetween( 'created_at' , [ Carbon::parse( $from_date )->copy()->startOfDay() , Carbon::parse( $to_date )->copy()->endOfDay() ] );
                               } )
                               ->when( $query , function ($q) use ($query) {
                                   $q->where( 'name' , 'ilike' , '%' . $query . '%' )
                                     ->orWhere( 'note' , 'ilike' , '%' . $query . '%' );
                               } )
                               ->when( is_numeric( $category ) , function ($query) use ($category) {
                                   $query->where( 'expense_category_id' , $category );
                               } )->orderBy( $orderColumn , $orderType )->paginate( $perPage , [ '*' ] , 'page' , $page );

                return ExpenseResource::collection( $data );
            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function perCategoryExpenses(PaginateRequest $request)
        {
            try {
                $from_date = $request->start;
                $to_date   = $request->end;
                $category  = $request->category;
                $query     = $request->input( 'query' );
                $page      = $request->get( 'page' ) ?? 1;
                $perPage   = $request->get( 'perPage' ) ?? 10;

                $expenses = Expense::query()
                                   ->select( 'expense_category_id' )
                                   ->selectRaw( 'COUNT(id) as count' )
                                   ->selectRaw( 'SUM(amount) as amount' )
                                   ->selectRaw( 'SUM(paid) as paid' )
                                   ->when( $from_date && ! $to_date , function ($query) use ($from_date) {
                                       $query->whereDate( 'created_at' , '>=' , Carbon::parse( $from_date )->copy()->startOfDay() );
                                   } )
                                   ->when( $from_date && $to_date , function ($query) use ($from_date , $to_date) {
                                       $query->whereBetween( 'date' , [ Carbon::parse( $from_date )->copy()->startOfDay() , Carbon::parse( $to_date )->copy()->endOfDay() ] );
                                   } )
                                   ->when( $query , function ($q) use ($query) {
                                       $q->where( 'name' , 'ilike' , '%' . $query . '%' )
                                         ->orWhere( 'note' , 'ilike' , '%' . $query . '%' );
                                   } )
                                   ->when( is_numeric( $category ) , function ($query) use ($category) {
                                       $query->where( 'expense_category_id' , $category );
                                   } )
                                   ->groupBy( 'expense_category_id' )
                                   ->with( 'expenseCategory' )
                                   ->orderByDesc( 'amount' )
                                   ->paginate( $perPage , [ '*' ] , 'page' , $page );

                return $expenses->through( function ($row) {
                    return [
                        'name'   => $row->expenseCategory->name ?? 'Uncategorized' ,
                        'count'  => $row->count ,
                        'paid'   => AppLibrary::currencyAmountFormat( $row->paid ) ,
                        'amount' => AppLibrary::currencyAmountFormat( $row->amount ) ,
                    ];
                } );

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function indexPerPaymentMethod(PaginateRequest $request)
        {
            try {
                $from_date = $request->start;
                $to_date   = $request->end;
                $category  = $request->category;
                $query     = $request->input( 'query' );
                $page      = $request->get( 'page' ) ?? 1;
                $perPage   = $request->get( 'perPage' ) ?? 10;

                $expenses = ExpensePayment::query()
                                          ->select( 'payment_method_id' )
                                          ->selectRaw( 'COUNT(id) as count' )
                                          ->selectRaw( 'SUM(amount) as total' )
                                          ->when( $from_date && $to_date , function ($query) use ($from_date , $to_date) {
                                              $query->whereBetween( 'date' , [ Carbon::parse( $from_date )->copy()->startOfDay() , Carbon::parse( $to_date )->copy()->endOfDay() ] );
                                          } )
                                          ->when( $from_date && ! $to_date , function ($query) use ($from_date) {
                                              $query->whereDate( 'created_at' , '>=' , Carbon::parse( $from_date )->copy()->startOfDay() );
                                          } )
                                          ->when( $query , function ($q) use ($query) {
                                              $q->where( 'name' , 'ilike' , '%' . $query . '%' )
                                                ->orWhere( 'note' , 'ilike' , '%' . $query . '%' );
                                          } )
                                          ->when( is_numeric( $category ) , function ($query) use ($category) {
                                              $query->where( 'expense_category_id' , $category );
                                          } )
                                          ->groupBy( 'payment_method_id' )
                                          ->with( 'method' )
                                          ->orderByDesc( 'total' )
                                          ->paginate( $perPage , [ '*' ] , 'page' , $page );

                return $expenses->through( function ($row) {
                    return [
                        'method' => $row->method->name ?? 'Unknown' ,
                        'count'  => $row->count ,
                        'total'  => AppLibrary::currencyAmountFormat( $row->total ) ,
                    ];
                } );

            } catch ( Exception $exception ) {
                Log::info( $exception->getMessage() );
                throw new Exception( $exception->getMessage() , 422 );
            }
        }

        public function indexTrend(Request $request)
        {
            try {
                $from_date = $request->start;
                $to_date   = $request->end;

                $from_date   = $request->start;
                $to_date     = $request->end;
                $category    = $request->category;
                $query       = $request->input( 'query' );

                $startDate = $from_date ? Carbon::parse( $from_date )->copy()->startOfDay() : Carbon::now()->startOfMonth();
                $endDate   = $to_date ? Carbon::parse( $to_date )->copy()->endOfDay() : Carbon::now()->endOfMonth();

                $diffInDays = $startDate->diffInDays( $endDate );

                if ( $diffInDays > 31 ) {
                    // Monthly grouping
                    $expenses = Expense::query()
                                       ->selectRaw( "TO_CHAR(date, 'YYYY-MM') as date" )
                                       ->selectRaw( 'SUM(amount) as amount' )
                        ->when( $from_date && ! $to_date , function ($query) use ($from_date) {
                            $query->whereDate( 'created_at' , '>=' , Carbon::parse( $from_date )->copy()->startOfDay() );
                        } )
                        ->when( $query , function ($q) use ($query) {
                            $q->where( 'name' , 'ilike' , '%' . $query . '%' )
                              ->orWhere( 'note' , 'ilike' , '%' . $query . '%' );
                        } )
                        ->when( is_numeric( $category ) , function ($query) use ($category) {
                            $query->where( 'expense_category_id' , $category );
                        } )
                                       ->whereBetween( 'date' , [ $startDate , $endDate ] )
                                       ->groupBy( 'date' )
                                       ->orderBy( 'date' )
                                       ->get();

                    return $this->response( TRUE , 'success' , $expenses->map( function ($row) {
                        return [
                            'date'   => Carbon::parse( $row->date )->format( 'M Y' ) ,
                            'amount' => (int) $row->amount ,
                        ];
                    } )->values()->toArray() );
                }
                else {
                    // Daily grouping
                    $expenses = Expense::query()
                                       ->selectRaw( 'DATE(date) as date' )
                                       ->selectRaw( 'SUM(amount) as amount' )
                                       ->whereBetween( 'date' , [ $startDate , $endDate ] )
                                       ->groupBy( 'date' )
                                       ->orderBy( 'date' )
                                       ->get();

                    return $this->response( TRUE , 'success' , $expenses->map( function ($row) {
                        return [
                            'date'   => Carbon::parse( $row->date )->format( 'M d' ) ,
                            'amount' => (int) $row->amount ,
                        ];
                    } )->values()->toArray() );
                }

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

                ExpenseTitle::firstOrCreate( [ 'name' => $request->name ] , [ 'name' => $request->name ] );

                $expense->update( [ 'expense_id' => recordId( 'EXP-' , $expense ) ] );

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
                        $expense->delete();
                    }
                } );
                return $this->response( TRUE , 'success' );
            } catch ( \Throwable $e ) {
                return $this->response( message: $e->getMessage() );
            }
        }

        public function expenseTitles()
        {
            return ExpenseTitleResource::collection( ExpenseTitle::all() );
        }
    }
