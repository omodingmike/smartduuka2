<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\CleaningServiceCustomerRequest;
    use App\Http\Resources\CleaningServiceCustomerResource;
    use App\Models\CleaningServiceCustomer;
    use Illuminate\Http\Request;

    class CleaningServiceCustomerController extends Controller
    {
        public function index(Request $request)
        {
            $query = CleaningServiceCustomer::query();
            $name  = $request->input( 'query' );
            $query->when( $name , function ($query) use ($name) {
                $query->where( 'name' , 'ilike' , "%{$name}%" );
            } );
            return CleaningServiceCustomerResource::collection( $query->get() );
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
