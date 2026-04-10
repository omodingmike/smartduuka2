<?php

    namespace App\Http\Controllers;

    use App\Enums\MediaEnum;
    use App\Http\Requests\FundsTransferRequest;
    use App\Http\Requests\StorePaymentMethodRequest;
    use App\Http\Requests\UpdatePaymentMethodRequest;
    use App\Http\Resources\PaymentMethodResource;
    use App\Models\PaymentMethod;
    use App\Models\PaymentMethodTransaction;
    use App\Traits\HasAdvancedFilter;
    use App\Traits\SaveMedia;
    use Illuminate\Http\Request;

    class PaymentMethodController extends Controller
    {
        use HasAdvancedFilter , SaveMedia;

        public function index(Request $request)
        {
            return PaymentMethodResource::collection( PaymentMethod::with( 'transactions' )->get() );
        }

        public function store(StorePaymentMethodRequest $request)
        {
            $method = PaymentMethod::create( $request->validated() );
            $this->saveMedia( $request , $method , MediaEnum::IMAGES_COLLECTION );
            activityLog( "Created Payment Method: $method->name" );
            return PaymentMethodResource::collection( PaymentMethod::all() );
        }

        public function update(UpdatePaymentMethodRequest $request , PaymentMethod $method)
        {
            $method->update( $request->validated() );
            $this->saveMedia( $request , $method , MediaEnum::IMAGES_COLLECTION );
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

        public function transfer(FundsTransferRequest $request)
        {
            PaymentMethodTransaction::create( [
                'amount'            => -$request->input( 'amount' ) ,
                'charge'            => $request->input( 'charge' ) ,
                'description'       => $request->input( 'description' ) ,
                'payment_method_id' => $request->input( 'from_method_id' ) ,
            ] );

            PaymentMethodTransaction::create( [
                'amount'            => $request->input( 'amount' ) ,
                'charge'            => 0 ,
                'description'       => $request->input( 'description' ) ,
                'payment_method_id' => $request->input( 'to_method_id' ) ,
            ] );
        }
    }
