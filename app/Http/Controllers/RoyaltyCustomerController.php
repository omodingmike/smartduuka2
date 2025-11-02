<?php

    namespace App\Http\Controllers;

    use App\Enums\Status;
    use App\Http\Requests\EarnPointsRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\StoreRoyaltyCustomerRequest;
    use App\Http\Requests\UpdateRoyaltyCustomerRequest;
    use App\Http\Resources\OrderResource;
    use App\Http\Resources\RoyaltyCustomerQrcodeResource;
    use App\Http\Resources\RoyaltyCustomerResource;
    use App\Jobs\SendAdminRoyaltyCustomerRegistrationEmailJob;
    use App\Jobs\SendRoyaltyCustomerRegistrationEmailJob;
    use App\Models\ActivityLog;
    use App\Models\Order;
    use App\Models\RoyaltyCustomer;
    use App\Models\RoyaltyPackage;
    use App\Models\RoyaltyPointsLog;
    use App\Models\RoyaltyReferal;
    use App\Models\User;
    use DateTime;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\File;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\Str;
    use SimpleSoftwareIO\QrCode\Facades\QrCode;

    class RoyaltyCustomerController extends Controller
    {
        protected array $orderFilter  = [
            'order_serial_no' ,
            'user_id' ,
            'branch_id' ,
            'total' ,
            'order_type' ,
            'order_datetime' ,
            'payment_method' ,
            'payment_status' ,
            'status' ,
            'delivery_boy_id' ,
            'source' ,
            'dining_table_id'
        ];
        protected       $itemFilter   = [
            'name' ,
            'status' ,
            'customer_id' ,
            'package_id' ,
        ];
        protected array $exceptFilter = [
            'excepts'
        ];

        public function index(Request $request)
        {
            $requests    = $request->all();
            $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
            $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
            $orderColumn = $request->get('order_column') ?? 'id';
            $orderType   = $request->get('order_type') ?? 'desc';
            return RoyaltyCustomerResource::collection(RoyaltyCustomer::where(function ($query) use ($requests) {
                foreach ( $requests as $key => $request ) {
                    if ( in_array($key , $this->itemFilter) ) {
                        if ( $key == 'except' ) {
                            $explodes = explode('|' , $request);
                            if ( count($explodes) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where('id' , '!=' , $explode);
                                }
                            }
                        } else {
                            if ( $key == 'item_category_id' ) {
                                $query->where($key , $request);
                            } else {
                                $query->where($key , 'like' , '%' . $request . '%');
                            }
                        }
                    }
                }
            })->orderBy($orderColumn , $orderType)->$method(
                $methodValue
            ));
        }

        public function store(StoreRoyaltyCustomerRequest $request)
        {
            $packages = RoyaltyPackage::where('status' , Status::ACTIVE)->get();
            if ( $packages->count() > 0 ) {
                return response([ 'status' => false , 'message' => 'No active package found' ] , 422);
            }
            $dob      = DateTime::createFromFormat('Y-m-d' , $request->dob);
            $name     = explode(' ' , $request->name);
            $now      = ( new DateTime() )->getTimestamp();
            $part1    = (int) ( substr((string) $now , -6) );
            $part2    = (int) ( $dob->format('d') . $dob->format('m') . $dob->format('y') );
            $number   = $part2 + $part1 + random_int(100000 , 999999);
            $initials = strtoupper($name[0][0]) . strtoupper($name[1][0]);
            if ( Auth::user() ) {
                $request->validated()['status'] = 5;
            }
            $royaltyCustomer = RoyaltyCustomer::create(Arr::add($request->validated() , 'customer_id' , "M$number$initials"));

            $filename = Str::random(10) . '.png';
            $url      = URL::to('/') . '/royalty/customers/frontend/show/' . $royaltyCustomer->id;

            if ( ! File::exists(storage_path('app/public/qr_codes/')) ) {
                File::makeDirectory(storage_path('app/public/qr_codes/'));
            }
            QrCode::format('png')->size(200)->generate($url , storage_path('app/public/qr_codes/' . $filename));
            $royaltyCustomer->update([ 'qr_code' => 'storage/qr_codes/' . $filename ]);
            if ( $request->referer ) {
                RoyaltyReferal::create([
                    'customer_id' => $royaltyCustomer->customer_id ,
                    'referral_id' => $request->referer
                ]);
            }

            if ( Auth::user() ) {
                ActivityLog::create([
                    'user_id'   => Auth::user()->id ,
                    'user_type' => User::class ,
                    'action'    => "Created $royaltyCustomer->name  Royalty Customer" ,
                ]);
                SendAdminRoyaltyCustomerRegistrationEmailJob::dispatchAfterResponse($royaltyCustomer->email , $royaltyCustomer->name , $url , asset
                ($royaltyCustomer->qr_code));
            } else {
                SendRoyaltyCustomerRegistrationEmailJob::dispatchAfterResponse($royaltyCustomer->email , $royaltyCustomer->name);
            }
            return new RoyaltyCustomerResource($royaltyCustomer);
        }

        public function show(RoyaltyCustomer $royaltyCustomer)
        {
            return new RoyaltyCustomerResource($royaltyCustomer);
        }

        public function update(UpdateRoyaltyCustomerRequest $request , RoyaltyCustomer $royaltyCustomer)
        {
            $royaltyCustomer->update($request->validated());
            if ( Auth::user() ) {
                ActivityLog::create([
                    'user_id'   => Auth::user()->id ,
                    'user_type' => User::class ,
                    'action'    => "Updated $royaltyCustomer->name  Royalty Customer details" ,
                ]);
            }

            return new RoyaltyCustomerResource($royaltyCustomer);
        }

        public function destroy(RoyaltyCustomer $royaltyCustomer)
        {
            $royaltyCustomer->delete();
            if ( Auth::user() ) {
                ActivityLog::create([
                    'user_id'   => Auth::user()->id ,
                    'user_type' => User::class ,
                    'action'    => "Deleted $royaltyCustomer->name  Royalty Customer" ,
                ]);
            }
            return response()->noContent();
        }

        public function myOrders(
            PaginateRequest $request ,
            RoyaltyCustomer $customer
        ) : Response | AnonymousResourceCollection | Application | ResponseFactory {
            try {
                return OrderResource::collection($this->userOrders($request , $customer));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function myOrder(
            PaginateRequest $request ,
            RoyaltyCustomer $customer
        ) : Response | AnonymousResourceCollection | Application | ResponseFactory {
            try {
                return OrderResource::collection($this->userOrder($request , $customer));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function qrCode()
        {
            try {
                $filename = 'self_registration.png';
                $url      = URL::to('/') . '/royalty/customer/signup/register';
                if ( ! File::exists(storage_path('app/public/qr_codes/')) ) {
                    File::makeDirectory(storage_path('app/public/qr_codes/'));
                }
                if ( ! File::exists(storage_path('app/public/qr_codes/' . $filename)) ) {
                    QrCode::format('png')->size(200)->generate($url , storage_path('app/public/qr_codes/' . $filename));
                    return asset('storage/qr_codes/' . $filename);
                }
//                return asset('storage/qr_codes/' . $filename);
                return new RoyaltyCustomerQrcodeResource([
                    'qrcode' => asset('storage/qr_codes/' . $filename) ,
                    'url'    => $url
                ]);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function earnPoints(EarnPointsRequest $request , RoyaltyCustomer $customer)
        {
            $points = $request->validated()['points'];
            $customer->increment('points' , $points);
            RoyaltyPointsLog::create([
                'customer_id' => $customer->id ,
                'points'      => $points ,
                'earned_by'   => auth()->id() ,
                'type'        => 'Earned Points'
            ]);

            ActivityLog::create([
                'user_id'   => Auth::user()->id ,
                'user_type' => User::class ,
                'action'    => "Earned $points points for $customer->name" ,
            ]);

            return new RoyaltyCustomerResource($customer);
        }

        public function userOrders(PaginateRequest $request , RoyaltyCustomer $user)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_by') ?? 'desc';

                return Order::where(function ($query) use ($requests , $user) {
                    $query->where('user_id' , $user->id)
                          ->where('user_type' , '=' , RoyaltyCustomer::class);
                    foreach ( $requests as $key => $request ) {
                        if ( in_array($key , $this->orderFilter) ) {
                            $query->where($key , 'like' , '%' . $request . '%');
                        }
                        if ( in_array($key , $this->exceptFilter) ) {
                            $explodes = explode('|' , $request);
                            if ( is_array($explodes) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where('status' , '!=' , $explode);
                                }
                            }
                        }
                    }
                })->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function pointsLog(PaginateRequest $request , RoyaltyCustomer $customer)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_by') ?? 'desc';

                return RoyaltyPointsLog::with([ 'customer' , 'earnedBy' , 'redeemedBy' ])->where(function ($query) use ($requests , $customer) {
                    $query->where('customer_id' , $customer->id);
                    foreach ( $requests as $key => $request ) {
                        if ( in_array($key , $this->orderFilter) ) {
                            $query->where($key , 'like' , '%' . $request . '%');
                        }
                        if ( in_array($key , $this->exceptFilter) ) {
                            $explodes = explode('|' , $request);
                            if ( is_array($explodes) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where('status' , '!=' , $explode);
                                }
                            }
                        }
                    }
                })->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function userOrder(PaginateRequest $request , RoyaltyCustomer $user)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_by') ?? 'desc';

                return Order::where(function ($query) use ($requests , $user) {
                    $query->where('user_id' , $user->id)
                          ->where('user_type' , '=' , RoyaltyCustomer::class);
                    foreach ( $requests as $key => $request ) {
                        if ( in_array($key , $this->orderFilter) ) {
                            $query->where($key , 'like' , '%' . $request . '%');
                        }
                        if ( in_array($key , $this->exceptFilter) ) {
                            $explodes = explode('|' , $request);
                            if ( is_array($explodes) ) {
                                foreach ( $explodes as $explode ) {
                                    $query->where('status' , '!=' , $explode);
                                }
                            }
                        }
                    }
                })->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                );
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }
    }
