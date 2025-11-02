<?php

    namespace App\Http\Controllers;

    use App\Http\Requests\StoreActivityLogRequest;
    use App\Http\Requests\UpdateActivityLogRequest;
    use App\Http\Resources\ActivityLogResource;
    use App\Models\ActivityLog;
    use Exception;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;

    class ActivityLogController extends Controller
    {
        protected array $activity_log_filter = [
            'user_id' ,
            'action' ,
            'from_date' ,
            'to_date' ,
        ];
        protected array $exceptFilter        = [
            'excepts'
        ];

        public function index(Request $request)
        {
            try {
                $requests    = $request->all();
                $method      = $request->get('paginate' , 0) == 1 ? 'paginate' : 'get';
                $methodValue = $request->get('paginate' , 0) == 1 ? $request->get('per_page' , 10) : '*';
                $orderColumn = $request->get('order_column') ?? 'id';
                $orderType   = $request->get('order_by') ?? 'desc';

                return ActivityLogResource::collection(ActivityLog::with('user')->where(function ($query) use ($requests) {
                    if ( isset($requests['from_date']) && isset($requests['to_date']) ) {
                        $first_date = Date('Y-m-d' , strtotime($requests['from_date']));
                        $last_date  = Date('Y-m-d' , strtotime($requests['to_date']));
                        $query->whereDate('created_at' , '>=' , $first_date)->whereDate(
                            'created_at' ,
                            '<=' ,
                            $last_date
                        );
                    }
                    if ( isset($requests['action']) ) {
                        $query->where('action' , 'like' , '%' .$requests['action'] . '%');
                    }
                    if ( isset($requests['user_id']) ) {
                        $query->where('user_id' , 'like' , '%' .$requests['user_id'] . '%');
                    }


//                    foreach ( $requests as $key => $request ) {
//                        if ( in_array($key , $this->activity_log_filter) ) {
//                            if ( $key === 'status' ) {
//                                $query->where($key , (int) $request);
//                            } else {
//                                $query->where($key , 'like' , '%' . $request . '%');
//                            }
//                        }
//
//                        if ( in_array($key , $this->exceptFilter) ) {
//                            $explodes = explode('|' , $request);
//                            if ( is_array($explodes) ) {
//                                foreach ( $explodes as $explode ) {
//                                    $query->where('order_type' , '!=' , $explode);
//                                }
//                            }
//                        }
//                    }
                })->orderBy($orderColumn , $orderType)->$method(
                    $methodValue
                ));
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }

        }

        public function store(StoreActivityLogRequest $request)
        {
            return new ActivityLogResource(ActivityLog::create($request->validated()));
        }


        public function show(ActivityLog $activityLog)
        {
            return new ActivityLogResource($activityLog);
        }

        public function update(UpdateActivityLogRequest $request , ActivityLog $activityLog)
        {
            return new ActivityLogResource(tap($activityLog)->update($request->validated()));
        }

        public function destroy(ActivityLog $activityLog)
        {
            $activityLog->delete();
            return new ActivityLogResource($activityLog);
        }
    }
