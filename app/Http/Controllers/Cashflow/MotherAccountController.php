<?php

    namespace App\Http\Controllers\Cashflow;

    use App\Http\Requests\Cashflow\MotherAccountRequest;
    use App\Http\Resources\AccountsResource;
    use App\Http\Resources\Cashflow\MotherAccountResource;
    use App\Models\Cashflow\MotherAccount;
    use App\Models\Cashflow\SubAccount;

    class MotherAccountController extends Controller
    {
        public function index()
        {
            return MotherAccountResource::collection( MotherAccount::all() );
        }

        public function store(MotherAccountRequest $request)
        {
            return new MotherAccountResource( MotherAccount::create( $request->validated() ) );
        }

        public function show(MotherAccount $motherAccount)
        {
            return new MotherAccountResource( $motherAccount );
        }

        public function update(MotherAccountRequest $request , MotherAccount $motherAccount)
        {
            $motherAccount->update( $request->validated() );

            return new MotherAccountResource( $motherAccount );
        }

        public function destroy(MotherAccount $motherAccount)
        {
            $motherAccount->delete();

            return response()->json();
        }

        public function all()
        {
            $motherAccounts = MotherAccount::select( 'id' , 'name' )->get()->map( function ($account) {
                return [
                    'id'   => 'mother-' . $account->id ,
                    'name' => $account->name ,
                ];
            } );

            $subAccounts = SubAccount::select( 'id' , 'name' )->get()->map( function ($account) {
                return [
                    'id'   => 'sub-' . $account->id ,
                    'name' => $account->name ,
                ];
            } );

            $merged = $motherAccounts
                ->concat( $subAccounts )
                ->sortBy( 'name' )
                ->values();

            return AccountsResource::collection( $merged);
        }
    }
