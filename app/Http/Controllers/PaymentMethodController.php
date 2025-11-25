<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StorePaymentMethodRequest;
    use App\Http\Requests\UpdatePaymentMethodRequest;
    use App\Http\Resources\PaymentMethodResource;
    use App\Models\PaymentMethod;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;

    class PaymentMethodController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            $filtered = $this->filter( new PaymentMethod() , $request , [ 'name' , 'code' ] );
            return PaymentMethodResource::collection( $filtered );
        }

        public function store(StorePaymentMethodRequest $request)
//        public function store(Request $request)
        {
//            info($request->all());
            $method = PaymentMethod::create( $request->validated() );
            activityLog( "Created Payment Method: $method->name" );
            return PaymentMethodResource::collection( PaymentMethod::all() );
        }

        public function update(UpdatePaymentMethodRequest $request , PaymentMethod $method)
        {
            $method->update( $request->validated() );
            activityLog( "Updated Payment Method: $method->name" );
            return PaymentMethodResource::collection( PaymentMethod::all() );
        }

        public function destroy(PaymentMethod $method)
        {
            $method->delete();
            activityLog( "Deleted Payment Method: $method->name" );
            return PaymentMethodResource::collection( PaymentMethod::all() );
        }

        public function deleteMethods(Request $request)
        {
            PaymentMethod::destroy( $request->get( 'ids' ) );
        }
    }
