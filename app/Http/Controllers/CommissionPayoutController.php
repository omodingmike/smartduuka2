<?php

    namespace App\Http\Controllers;

    use App\Enums\Role as EnumRole;
    use App\Http\Requests\CommissionPayoutRequest;
    use App\Http\Resources\CommissionPayoutResource;
    use App\Models\CommissionPayout;
    use App\Models\Expense;
    use App\Models\ExpenseCategory;
    use App\Models\User;
    use Illuminate\Support\Facades\DB;
    use Spatie\Permission\Models\Role;

    class CommissionPayoutController extends Controller
    {
        public function index()
        {
            $payouts = CommissionPayout::with( [ 'user' , 'role' ] )->orderBy( 'created_at' , 'desc' )->get();

            return CommissionPayoutResource::collection( $payouts );
        }


        public function store(CommissionPayoutRequest $request)
        {
            return DB::transaction( function () use ($request) {
                $data = $request->validated();

                $data[ 'date' ] = date( 'Y-m-d H:i:s' , strtotime( $data[ 'date' ] ) );

                // === CASE 1: Specific user ===
                if ( $data[ 'applies_to' ] === 'user' && isset( $data[ 'user_id' ] ) ) {
                    $payout = CommissionPayout::create( [
                        'applies_to' => 'user' ,
                        'amount'     => $data[ 'amount' ] ,
                        'user_id'    => $data[ 'user_id' ] ,
                        'date'       => $data[ 'date' ] ,
                    ] );
                    $this->addExpense( $payout );
                }

                // === CASE 2: All users ===
                if ( $data[ 'applies_to' ] === 'users' ) {
                    $users = User::whereHas( 'roles' , function ($query) {
                        $query->where( 'id' , '=' , EnumRole::DISTRIBUTOR );
                    } );

                    foreach ( $users as $user ) {
                        $payout = CommissionPayout::create( [
                            'applies_to' => 'users' ,
                            'amount'     => $data[ 'amount' ] ,
                            'user_id'    => $user->id ,
                            'date'       => $data[ 'date' ] ,
                        ] );
                        $this->addExpense( $payout );
                    }
                }

                // === CASE 3: Role ===
                if ( $data[ 'applies_to' ] === 'role' && isset( $data[ 'role_id' ] ) ) {
                    $role = Role::with( 'users' )->find( $data[ 'role_id' ] );

                    if ( $role ) {
                        foreach ( $role->users as $user ) {
                            $payout = CommissionPayout::create( [
                                'applies_to' => 'role' ,
                                'amount'     => $data[ 'amount' ] ,
                                'user_id'    => $user->id ,
                                'role_id'    => $role->id ,
                                'date'       => $data[ 'date' ] ,
                            ] );
                            $this->addExpense( $payout );
                        }
                    }
                }

                return response()->json( [] , 204 );
            } );
        }

        private function addExpense(CommissionPayout $payout)
        {
            $user = User::find( $payout->user_id );

            Expense::create( [
                'name'          => "Commission payout to $user->name" ,
                'amount'        => $payout->amount ,
                'date'          => $payout->date ,
                'category'      => ExpenseCategory::where( 'name' , 'Commission Payouts' )->first()->id ,
                'paymentMethod' => 0 ,
                'referenceNo'   => 'E' . time() ,
                'isRecurring'   => 0 ,
                'recurs'        => 0 ,
                'user_id'       => auth()->id() ,
                'repetitions'   => 0 ,
                'repeats_on'    => NULL ,
                'paid'          => $payout->amount ,
                'paid_on'       => $payout->date ,
            ] );
        }

        public function destroy(CommissionPayout $commissionPayout)
        {
            $commissionPayout->delete();

            return response()->json();
        }
    }
