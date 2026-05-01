<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\LegacyDebtRequest;
    use App\Http\Resources\LegacyDebtResource;
    use App\Models\CustomerLedger;
    use App\Models\LegacyDebt;
    use App\Models\User;

    class LegacyDebtController extends Controller
    {
        public function index()
        {
            return LegacyDebtResource::collection( LegacyDebt::all() );
        }

        public function store(LegacyDebtRequest $request)
        {
            $debts = json_decode( $request->debts , TRUE );
            foreach ( $debts as $debt ) {
                $user = User::find( $debt [ 'user_id' ] );
                LegacyDebt::create( [
                    'user_id' => $user->id ,
                    'amount'  => $debt[ 'amount' ] ,
                    'date'    => $debt[ 'date' ] ,
                    'notes'   => $debt[ 'notes' ] ,
                ] );
                $user->refresh();
                CustomerLedger::create( [
                    'user_id'     => $user->id ,
                    'date'        => now() ,
                    'reference'   => time() ,
                    'description' => 'Legacy Debt' ,
                    'bill_amount' => $debt[ 'amount' ] ,
                    'paid'        => 0 ,
                    'balance'     => userCredit( $user)
                ] );
            }
            return response()->json();
        }

        public function show(LegacyDebt $legacyDebt)
        {
            return new LegacyDebtResource( $legacyDebt );
        }

        public function update(LegacyDebtRequest $request , LegacyDebt $legacyDebt)
        {
            $legacyDebt->update( $request->validated() );

            return new LegacyDebtResource( $legacyDebt );
        }

        public function destroy(LegacyDebt $legacyDebt)
        {
            $legacyDebt->delete();

            return response()->json();
        }
    }
