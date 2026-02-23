<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\ExpensePaymentRequest;
    use App\Models\Expense;
    use App\Models\ExpensePayment;
    use App\Models\PaymentMethodTransaction;
    use App\Traits\ApiResponse;
    use App\Traits\AuthUser;
    use App\Traits\FilesTrait;
    use Exception;
    use Illuminate\Support\Facades\DB;

    class ExpensePaymentController extends Controller
    {
        use  ApiResponse , FilesTrait , AuthUser;

        public function index()
        {
            return $this->response( success: TRUE , message: 'success' , data: ExpensePayment::where( 'expense_id' , request()->expense_id )->get() );
        }

        public function store(ExpensePaymentRequest $request , Expense $expense)
        {
            try {
                DB::transaction( function () use ($request , $expense) {
                    $expense->increment( 'paid' , $request->amount );
                    $validated = $request->validated();

                    ExpensePayment::create( [
                        'amount'        => $validated[ 'amount' ] ,
                        'date'          => now() ,
                        'paymentMethod' => $validated[ 'method' ] ,
                        'expense_id'    => $validated[ 'expenseId' ] ,
                        'register_id'   => register()->id
                    ] );

                    PaymentMethodTransaction::create( [
                        'amount'            => -$validated[ 'amount' ] ,
                        'item_id'           => $expense->id ,
                        'item_type'         => Expense::class ,
                        'charge'            => 0 ,
                        'description'       => 'Expense Payment #' . $expense->name ,
                        'payment_method_id' => $validated[ 'method' ] ,
                    ] );

                    activityLog( "Made payment for {$expense->name}" );

                } );
                return $this->response( success: TRUE , message: 'success' );
            } catch ( Exception $e ) {
                return $this->response( message: $e->getMessage() );
            }
        }
    }
