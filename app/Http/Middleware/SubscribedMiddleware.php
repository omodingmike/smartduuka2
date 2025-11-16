<?php

    namespace App\Http\Middleware;

    use App\Models\Subscription;
    use Closure;
    use Illuminate\Http\Request;
    use Symfony\Component\HttpFoundation\Response;

    class SubscribedMiddleware
    {
        public function handle(Request $request , Closure $next) : Response
        {
//            $subscription = Subscription::where('expires_at' , '>=' , now())
//                                        ->where('status' , 'active')
//                                        ->where('project_id' , config('app.project_id'))
//                                        ->exists();
//            if ( ! $subscription ) {
//                return response()->json([
//                    'message' => 'Your subscription has expired. Please renew your subscription.'
//                ] , 402);
//            }
            return $next($request);
        }
    }
