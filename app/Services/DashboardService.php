<?php

    namespace App\Services;

    use App\Enums\OrderStatus;
    use App\Enums\OrderType;
    use App\Enums\PaymentStatus;
    use App\Enums\Role as EnumRole;
    use App\Enums\Status;
    use App\Libraries\AppLibrary;
    use App\Models\Order;
    use App\Models\Product;
    use App\Models\Purchase;
    use App\Models\Stock;
    use App\Models\User;
    use Carbon\Carbon;
    use Exception;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Log;

    class DashboardService
    {

        public function salesSummary(Request $request)
        {
            $order = new Order;
            if ( $request->first_date && $request->last_date ) {
                $first_date = Date('Y-m-d' , strtotime($request->first_date));
                $last_date  = Date('Y-m-d' , strtotime($request->last_date));
            } else {
                $first_date = Date('Y-m-01' , strtotime(Carbon::today()->toDateString()));
                $last_date  = Date('Y-m-t' , strtotime(Carbon::today()->toDateString()));
            }

            $date      = date_diff(date_create($first_date) , date_create($last_date) , false);
            $date_diff = (int) $date->format("%a");

            $total_sales = AppLibrary::flatAmountFormat($order->whereDate('order_datetime' , '>=' , $first_date)->whereDate('order_datetime' , '<=' , $last_date)->where('payment_status' , PaymentStatus::PAID)->sum('total'));

            $dateRangeArray = [];
            for ( $currentDate = strtotime($first_date) ; $currentDate <= strtotime($last_date) ; $currentDate += ( 86400 ) ) {

                $date             = date('Y-m-d' , $currentDate);
                $dateRangeArray[] = $date;
            }

            $dateRangeValueArray = [];
            for ( $i = 0 ; $i <= count($dateRangeArray) - 1 ; $i++ ) {
                $per_day               = AppLibrary::flatAmountFormat($order->whereDate('order_datetime' , $dateRangeArray[$i])->where('payment_status' , PaymentStatus::PAID)->sum('total'));
                $dateRangeValueArray[] = floatval($per_day);
            }


            $salesSummaryArray = [];
            if ( $date_diff > 0 ) {
                $salesSummaryArray['total_sales']   = AppLibrary::currencyAmountFormat($total_sales);
                $salesSummaryArray['avg_per_day']   = AppLibrary::currencyAmountFormat($total_sales / $date_diff);
                $salesSummaryArray['per_day_sales'] = $dateRangeValueArray;
            } else {
                $salesSummaryArray['total_sales']   = AppLibrary::currencyAmountFormat($total_sales);
                $salesSummaryArray['avg_per_day']   = AppLibrary::currencyAmountFormat($total_sales);
                $salesSummaryArray['per_day_sales'] = $dateRangeValueArray;
            }

            return $salesSummaryArray;
        }

        public function customerStates(Request $request)
        {
            $order = new Order;
            if ( $request->first_date && $request->last_date ) {
                $first_date = Date('Y-m-d' , strtotime($request->first_date));
                $last_date  = Date('Y-m-d' , strtotime($request->last_date));
            } else {
                $first_date = Date('Y-m-01' , strtotime(Carbon::today()->toDateString()));
                $last_date  = Date('Y-m-t' , strtotime(Carbon::today()->toDateString()));
            }

            $timeArray = [ "06:00" , "07:00" , "08:00" , "09:00" , "10:00" , "11:00" , "12:00" , "13:00" , "14:00" , "15:00" , "16:00" , "17:00" , "18:00" , "19:00" , "20:00" , "21:00" , "22:00" , "23:00" ];

            $customerSateArray  = [];
            $totalCustomerArray = [];
            $first_time         = "";
            $last_time          = "";
            for ( $i = 0 ; $i <= count($timeArray) - 1 ; $i++ ) {
                $first_time = date('H:i' , strtotime($timeArray[$i]));
                $last_time  = date('H:i' , strtotime($timeArray[$i] . ' +59 minutes'));

                $total_customer       = $order->whereDate('order_datetime' , '>=' , $first_date)->whereDate('order_datetime' , '<=' , $last_date)->whereTime('order_datetime' , '>=' , Carbon::parse($first_time))->whereTime('order_datetime' , '<=' , Carbon::parse($last_time))->get()->count();
                $totalCustomerArray[] = $total_customer;
            }

            $customerSateArray['total_customers'] = $totalCustomerArray;
            $customerSateArray['times']           = $timeArray;

            return $customerSateArray;
        }

        public function totalSales(Request $request)
        {
            try {
                return Order::where('payment_status' , PaymentStatus::PAID)
                            ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                $start_date = Carbon::parse($request->first_date);
                                $last_date  = Carbon::parse($request->last_date);
                                $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                            })->sum('total');
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function totalOrders(Request $request)
        {
            try {
                return Order::where('status' , OrderStatus::DELIVERED)
                            ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                $start_date = Carbon::parse($request->first_date);
                                $last_date  = Carbon::parse($request->last_date);
                                $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                            })->count();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function totalCustomers(Request $request)
        {
            try {
                return User::role(EnumRole::CUSTOMER)
                           ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                               $start_date = Carbon::parse($request->first_date);
                               $last_date  = Carbon::parse($request->last_date);
                               $query->whereBetween('created_at' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                           })->count();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function totalProducts(Request $request)
        {
            try {
                return Product::when($request->first_date && $request->last_date , function ($query) use ($request) {
                    $start_date = Carbon::parse($request->first_date);
                    $last_date  = Carbon::parse($request->last_date);
                    $query->whereBetween('created_at' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                })->count();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }


        public function grossProfit(Request $request)
        {
            try {
                $orders       = Order::with('orderProducts.product')
                                     ->where('payment_status' , PaymentStatus::PAID)
                                     ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                         $start_date = Carbon::parse($request->first_date);
                                         $last_date  = Carbon::parse($request->last_date);
                                         $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                                     })->get();
                $gross_profit = 0;
                foreach ( $orders as $order ) {
                    $grossProfit  = $order->orderProducts->sum(function ($product) {
                        return $product->product->selling_price;
                    });
                    $gross_profit += $grossProfit;
                }
                return $gross_profit;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function netProfit(Request $request)
        {
            try {
                $orders = Order::with('orderProducts.product')
                               ->where('payment_status' , PaymentStatus::PAID)
                               ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                   $start_date = Carbon::parse($request->first_date);
                                   $last_date  = Carbon::parse($request->last_date);
                                   $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                               })->get();

                $net_profit = 0;
                foreach ( $orders as $order ) {
                    $netProfit  = $order->orderProducts->sum(function ($product) {
                        return $product->product->selling_price - $product->product->buying_price;
                    });
                    $net_profit += $netProfit;
                }
                return $net_profit;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function stockValue(Request $request)
        {
            try {
                $stock_value = 0;
                Stock::where('status' , Status::ACTIVE)
                     ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                         $start_date = Carbon::parse($request->first_date);
                         $last_date  = Carbon::parse($request->last_date);
                         $query->whereBetween('created_at' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                     })
                     ->chunkById(100 , function (Collection $stocks) use (&$stock_value) {
                         foreach ( $stocks as $stock ) {
                             if ( isset($stock->product->retail_unit_id) ) {
                                 $stock_value += $stock->quantity * $stock->product->retail_price_per_base_unit;
                             } else {
                                 $stock_value += $stock->quantity * $stock->price;
                             }
                         }
                     } , 'id');
                return $stock_value;
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function vendorBalance(Request $request)
        {
            try {
                return Purchase::when($request->first_date && $request->last_date , function ($query) use ($request) {
                    $start_date = Carbon::parse($request->first_date);
                    $last_date  = Carbon::parse($request->last_date);
                    $query->whereBetween('date' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                })->withTotalBalance();
            } catch ( Exception $exception ) {
                Log::error($exception->getMessage());
                return response()->json([ 'error' => $exception->getMessage() ] , 422);
            }
        }

        public function creditSales(Request $request)
        {
            try {
                $total_orders       = Order::where('order_type' , OrderType::CREDIT)
                                           ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                               $start_date = Carbon::parse($request->first_date);
                                               $last_date  = Carbon::parse($request->last_date);
                                               $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                                           })->count();
                $total_credit_sales = Order::where('order_type' , OrderType::CREDIT)
                                           ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                               $start_date = Carbon::parse($request->first_date);
                                               $last_date  = Carbon::parse($request->last_date);
                                               $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                                           })->sum('total');

                $credit_orders_paid = Order::where('order_type' , OrderType::CREDIT)
                                           ->withSum('creditDepositPurchases' , 'paid')
                                           ->get();

                $totalPaidForCreditOrders = 0;

                foreach ( $credit_orders_paid as $order ) {
                    $totalPaidForCreditOrders += $order->credit_deposit_purchases_sum_paid;
                }
                $credit_balance = $total_credit_sales - $totalPaidForCreditOrders;
                return [
                    'total_orders'       => $total_orders ,
                    'total_credit_sales' => AppLibrary::currencyAmountFormat($total_credit_sales) ,
                    'credit_paid'        => AppLibrary::currencyAmountFormat($totalPaidForCreditOrders) ,
                    'credit_balance'     => AppLibrary::currencyAmountFormat($credit_balance) ,
                ];

            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function depositSales(Request $request)
        {
            try {
                $total_orders       = Order::where('order_type' , OrderType::DEPOSIT)
                                           ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                               $start_date = Carbon::parse($request->first_date);
                                               $last_date  = Carbon::parse($request->last_date);
                                               $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                                           })->count();
                $total_credit_sales = Order::where('order_type' , OrderType::DEPOSIT)
                                           ->when($request->first_date && $request->last_date , function ($query) use ($request) {
                                               $start_date = Carbon::parse($request->first_date);
                                               $last_date  = Carbon::parse($request->last_date);
                                               $query->whereBetween('order_datetime' , [ $start_date->copy()->startOfDay() , $last_date->copy()->endOfDay() ]);
                                           })->sum('total');

                $deposit_orders_paid = Order::where('order_type' , OrderType::DEPOSIT)
                                            ->withSum('creditDepositPurchases' , 'paid')
                                            ->get();

                $totalPaidForDepositOrders = 0;

                foreach ( $deposit_orders_paid as $order ) {
                    $totalPaidForDepositOrders += $order->credit_deposit_purchases_sum_paid;
                }
                $credit_balance = $total_credit_sales - $totalPaidForDepositOrders;
                return [
                    'total_orders'        => $total_orders ,
                    'total_deposit_sales' => AppLibrary::currencyAmountFormat($total_credit_sales) ,
                    'deposit_paid'        => AppLibrary::currencyAmountFormat($totalPaidForDepositOrders) ,
                    'deposit_balance'     => AppLibrary::currencyAmountFormat($credit_balance) ,
                ];

            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function inStock(Request $request)
        {
            try {
                return Stock::where('quantity' , '>=' , 0)->count();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function outStock(Request $request)
        {
            try {
                return Stock::where('quantity' , '<=' , 0)->count();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }

        public function expiredStock(Request $request)
        {
            try {
                return Stock::where('expiry_date' , '>' , now()->copy()->endOfDay())->count();
            } catch ( Exception $exception ) {
                Log::info($exception->getMessage());
                throw new Exception($exception->getMessage() , 422);
            }
        }
    }
