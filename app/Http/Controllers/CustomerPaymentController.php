<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\CustomerPaymentResource;
    use App\Models\User;
    use Exception;
    use Illuminate\Support\Facades\Log;

    class CustomerPaymentController extends Controller
    {
        public function index(PaginateRequest $request , User $customer)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_type') ?? 'desc';

                return CustomerPaymentResource::collection($customer->payments()->with([ 'paymentMethod' ])->where(function ($query) use ($requests) {
                    if ( isset($requests['first_date']) && isset($requests['last_date']) ) {
                        $query->whereBetween('date' ,
                            [ toCarbonDate($requests['first_date'])->copy()->startOfDay() ,
                                toCarbonDate($requests['last_date'])->copy()->endOfDay()
                            ]);
                    }
//                    foreach ( $requests as $key => $request ) {
//                    if ( in_array($key , $this->userFilter) ) {
//                        $query->where($key , 'like' , '%' . $request . '%');
//                    }
//                    }
                })->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                ));
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }
    }
