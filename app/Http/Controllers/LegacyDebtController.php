<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\LegacyDebtRequest;
    use App\Http\Resources\LegacyDebtResource;
    use App\Models\LegacyDebt;

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
                LegacyDebt::create( [
                    'user_id' => $debt[ 'user_id' ] ,
                    'amount'  => $debt[ 'amount' ] ,
                    'date'    => $debt[ 'date' ] ,
                    'notes'   => $debt[ 'notes' ] ,
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
