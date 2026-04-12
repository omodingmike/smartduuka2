<?php

    namespace App\Http\Controllers\Cashflow;

    use App\Http\Resources\Cashflow\ActivityLogResource;
    use App\Traits\HasAdvancedFilter;
    use Illuminate\Http\Request;
    use Spatie\Activitylog\Models\Activity;

    class ActivityLogController extends Controller
    {
        use HasAdvancedFilter;

        public function index(Request $request)
        {
            $app_id = $request->header( 'X-App-Id' );
            $query  = Activity::where( 'properties->app_id' , $app_id )->whereNotNull( 'causer_id' );
            return ActivityLogResource::collection( $this->filter( $query , $request ) );
        }
    }
