<?php

    use App\Enums\OrderType;
    use App\Enums\StockStatus;
    use App\Events\TestEvent;
    use App\Http\Controllers\WhatsAppController;
    use App\Libraries\AppLibrary;
    use App\Models\Business;
    use App\Models\Damage;
    use App\Models\Expense;
    use App\Models\Order;
    use App\Models\Product;
    use App\Models\ProductVariation;
    use App\Models\Stock;
    use App\Models\Subscription;
    use App\Models\Tenant;
    use Illuminate\Database\Eloquent\Collection;
    use Illuminate\Http\Request;
    use Illuminate\Support\Arr;
    use Illuminate\Support\Facades\Schedule;

    $now = now( config( 'app.timezone' ) );

    // Schedule tenant backups
    Schedule::command( 'tenants:backup' )->hourly();

    // Schedule Reverb Test Event
//    Schedule::call(function () {
//        $tenantId = tenant('id') ?? 'central';
//        TestEvent::dispatch("Scheduled event for tenant [{$tenantId}] at " . now()->toDateTimeString());
//    })->everyMinute();

    Schedule::call(function () {
        Tenant::all()->runForEach(function ($tenant) {
            TestEvent::dispatch("Scheduled event for tenant [{$tenant->id}] at " . now()->toDateTimeString());
        });
    })->everyMinute();

    Schedule::call( function () use ($now) {
        Expense::where( [
            'isRecurring' => 1 ,
        ] )->where( 'repeats_on' , '<=' , $now->format( 'Y-m-d H:i:s' ) )
               ->chunkById( 100 , function (Collection $expenses) use ($now) {
                   $expenses->each( function ($expense) {} );
               } );
    } )->daily();
    if ( config( 'app.main_app' ) ) {
        Schedule::call( function () use ($now) {
            $request              = new Request();
            $whatsAppController   = new WhatsAppController();
            $expired_subscription = Subscription::whereDate( 'expires_at' , '<' , $now )
                                                ->where( 'status' , '<>' , 'expired' )
                                                ->latest()
                                                ->first();

            $expiring_soon_subscriptions = Subscription::where( 'expires_at' , '>=' , now() )
                                                       ->where( 'expires_at' , '<=' , now()->addDays( 7 ) )
                                                       ->where( 'status' , '<>' , 'expired' )
                                                       ->latest()
                                                       ->first();

            if ( $expiring_soon_subscriptions ) {
                $business = Business::where( 'project_id' , $expiring_soon_subscriptions->project_id )->first();
                if ( ! $business->reminder_sent ) {
                    $response   = $whatsAppController->sendTemplateMessage( $request->merge( [
                        'to'         => $business->phone_number ,
                        'template'   => 'sub_reminder' ,
                        'parameters' => [
                            [
                                'type'           => 'text' ,
                                'parameter_name' => 'name' ,
                                'text'           => $business->business_name ,
                            ] ,
                            [
                                'type'           => 'text' ,
                                'parameter_name' => 'date' ,
                                'text'           => AppLibrary::datetime2( $expiring_soon_subscriptions->expires_at ) ,
                            ] ,
                        ]
                    ] ) );
                    $message_id = Arr::get( $response , 'messages.0.id' );
                    if ( $message_id ) {
                        $business->update( [ 'reminder_sent' => TRUE ] );
                    }
                }
            }

            if ( $expired_subscription ) {
                $expired_subscription->update( [ 'status' => 'expired' ] );
                $business = Business::where( 'project_id' , $expired_subscription->project_id )->first();
                if ( ! $business->expired_sent ) {
                    $response   = $whatsAppController->sendTemplateMessage( $request->merge( [
                        'to'         => $business->phone_number ,
                        'template'   => 'expired' ,
                        'parameters' => [
                            [
                                'type'           => 'text' ,
                                'parameter_name' => 'name' ,
                                'text'           => $business->business_name ,
                            ] ,
                            [
                                'type'           => 'text' ,
                                'parameter_name' => 'date' ,
                                'text'           => AppLibrary::datetime2( $now ) ,
                            ] ,
                        ]
                    ] ) );
                    $message_id = Arr::get( $response , 'messages.0.id' );
                    if ( $message_id ) {
                        $business->update( [ 'expired_sent' => TRUE ] );
                    }
                }
            }
            Order::whereColumn( 'paid' , '>=' , 'total' )->update( [ 'order_type' => OrderType::IN_STORE ] );
        } )->everyFiveMinutes();

        Schedule::call( function () {
            updateCoa();
            Stock::where( 'expiry_date' , '<>' , NULL )
                 ->where( 'expiry_date' , '<' , now()->copy()->endOfDay() )
                 ->where( 'quantity' , '>' , 0 )
                 ->chunkById( 100 , function ($stocks) {
                     foreach ( $stocks as $stock ) {
                         $stock->quantity = -abs( $stock->quantity );
                         $stock->save();
                         $damage = Damage::create( [
                             'date'         => now() ,
                             'reference_no' => 'D' . time() ,
                             'subtotal'     => $stock->subtotal ,
                             'tax'          => $stock->tax ,
                             'discount'     => $stock->discount ,
                             'total'        => $stock->total ,
                             'note'         => 'Stock Expired' ,
                         ] );
                         if ( $stock->products ) {
                             $model_id = $damage->id;
                             foreach ( $stock->products as $product ) {
                                 $stock = Stock::create( [
                                     'model_type'      => Damage::class ,
                                     'model_id'        => $model_id ,
                                     'reference'       => 'D' . time() ,
                                     'item_type'       => count( $product->variations ) > 0 ? ProductVariation::class : Product::class ,
                                     'product_id'      => $product->id ,
                                     'variation_names' => 'variation_names' ,
                                     'item_id'         => $product->id ,
                                     'price'           => $product->buying_price ,
                                     'quantity'        => -$stock->quantity ,
                                     'discount'        => $stock->discount ,
                                     'tax'             => $stock->tax ,
                                     'subtotal'        => $stock->subtotal ,
                                     'total'           => $stock->total ,
                                     'sku'             => $product->sku ,
                                     'status'          => StockStatus::EXPIRED
                                 ] );
                             }
                         }
                     }
                 } );
        } )->everyMinute();

        Schedule::call( function () {
            $logFile = storage_path( 'logs/laravel.log' );
            if ( file_exists( $logFile ) ) {
                unlink( $logFile );
            }
        } )->dailyAt( '00:00' );

        Schedule::command( 'commissions:calculate' )->everyMinute();
    }
