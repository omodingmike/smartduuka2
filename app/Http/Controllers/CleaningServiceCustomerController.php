<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CleaningServiceCustomerRequest;
    use App\Http\Resources\CleaningServiceCustomerResource;
    use App\Models\CleaningServiceCustomer;

    class CleaningServiceCustomerController extends Controller
    {
        public function index()
        {
            return CleaningServiceCustomerResource::collection( CleaningServiceCustomer::all() );
        }

        public function store(CleaningServiceCustomerRequest $request)
        {
            return new CleaningServiceCustomerResource( CleaningServiceCustomer::create( $request->validated() ) );
        }

        public function show(CleaningServiceCustomer $cleaningServiceCustomer)
        {
            return new CleaningServiceCustomerResource( $cleaningServiceCustomer );
        }

        public function update(CleaningServiceCustomerRequest $request , CleaningServiceCustomer $cleaningServiceCustomer)
        {
            $cleaningServiceCustomer->update( $request->validated() );

            return new CleaningServiceCustomerResource( $cleaningServiceCustomer );
        }

        public function destroy(CleaningServiceCustomer $cleaningServiceCustomer)
        {
            $cleaningServiceCustomer->delete();

            return response()->json();
        }
    }
