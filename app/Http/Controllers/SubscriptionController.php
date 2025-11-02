<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\SubscriptionPlanResource;
    use App\Http\Resources\SubscriptionResource;
    use App\Models\Subscription;
    use App\Models\SubscriptionPlan;
    use App\Traits\ApiResponse;
    use Illuminate\Http\Request;

    class SubscriptionController extends Controller
    {
        use ApiResponse;

        public function index(PaginateRequest $request)
        {
            $project_id  = config('app.project_id');
            $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            return SubscriptionResource::collection(Subscription::where('project_id' , $project_id)->orderBy($orderColumn , $orderType)->$method($methodValue));
        }

        public function subscriptionPlans()
        {
            return SubscriptionPlanResource::collection(SubscriptionPlan::all());
        }

        public function hasActive()
        {
            $has_active = Subscription::where('expires_at' , '>=' , now())
                                      ->where('status' , 'active')
                                      ->where('project_id' , config('app.project_id'))
                                      ->exists();
            return $this->response(true , 'Subscription status' , $has_active);
        }

        public function store(Request $request)
        {
            $validator = validator($request->all() , [
                'id' => 'required|exists:subscription_plans,id'
            ]);
            if ( $validator->fails() ) {
                return $this->response(false , $validator->errors()->first());
            }
            $plan = Subscription::create([
                'user_id'    => auth()->id() ,
                'invoice'    => 'INV-' . time() ,
                'plan_id'    => $request->id ,
                'starts_at'  => now() ,
                'expires_at' => now()->addMonths($request->duration) ,
                'status'     => 2 ,
            ]);
            if ( $plan ) {
                return $this->response(true , 'Subscription created successfully' , $plan);
            }
            return $this->response(false , 'Subscription creation failed');
        }

        public function show(string $id)
        {
            //
        }

        public function update(Request $request , string $id)
        {
            //
        }

        public function destroy(string $id)
        {
            //
        }
    }
