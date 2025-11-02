<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\StorePaymentAccountRequest;
    use App\Http\Requests\UpdatePaymentAccountRequest;
    use App\Http\Resources\PaymentAccountResource;
    use App\Models\PaymentAccount;
    use Exception;
    use Illuminate\Support\Facades\Log;

    class PaymentAccountController extends Controller
    {
        public function index(PaginateRequest $request)
        {
            try {
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_type') ?? 'desc';
                return PaymentaccountResource::collection(PaymentAccount::with('currency')->orderBy($orderColumn , $orderType)->$method($methodValue));
            } catch ( \Exception $e ) {
                Log::info($e->getMessage());
                throw new Exception($e->getMessage() , 422);
            }
        }

        public function store(StorePaymentAccountRequest $request)
        {
            PaymentAccount::create($request->validated());
        }

        public function show(PaymentAccount $paymentAccount)
        {
            return new PaymentAccountResource($paymentAccount);
        }

        public function update(UpdatePaymentAccountRequest $request , PaymentAccount $paymentAccount)
        {
            $paymentAccount->update($request->validated());
        }

        public function destroy(PaymentAccount $paymentAccount)
        {
            $paymentAccount->delete();
        }
    }
