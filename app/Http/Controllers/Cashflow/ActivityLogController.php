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
            return ActivityLogResource::collection( $this->filter( Activity::whereNotNull( 'causer_id' ) , $request ) );
        }
    }
