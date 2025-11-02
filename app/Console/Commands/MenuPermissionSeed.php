<?php

    namespace App\Console\Commands;

    use App\Enums\Ask;
    use App\Enums\Role as EnumRole;
    use App\Enums\SettingsEnum;
    use App\Enums\Status;
    use App\Models\Business;
    use App\Models\Currency;
    use App\Models\ExpenseCategory;
    use App\Models\Order;
    use App\Models\PaymentAccount;
    use App\Models\PaymentMethod;
    use App\Models\ProductionProcess;
    use App\Models\Purchase;
    use App\Models\Stock;
    use App\Models\Subscription;
    use App\Models\Supplier;
    use App\Models\Warehouse;
    use Dipokhalder\EnvEditor\EnvEditor;
    use Dipokhalder\EnvEditor\Exceptions\EnvException;
    use Illuminate\Console\Command;
    use Illuminate\Support\Facades\Artisan;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Str;
    use Laravel\Sanctum\PersonalAccessToken;
    use Smartisan\Settings\Facades\Settings;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;

    class MenuPermissionSeed extends Command
    {
        protected $signature   = 'mp:seed';
        protected $description = 'Command description';

        /**
         * @throws EnvException
         */
        public function handle() : int
        {
            $distributor_role = Role::find( EnumRole::DISTRIBUTOR );
            if ( ! $distributor_role ) {
                Role::create( [
                    'name'       => 'Distributor' ,
                    'guard_name' => 'sanctum'
                ] );
            }


            $env_editor = new EnvEditor;
            if ( ! config( 'system.quotations' ) ) {
                Settings::group( SettingsEnum::APP_SETTINGS() )->set( [ 'a4_receipt' => Ask::NO ] );
            }
            $data = [];
            if ( config( 'app.main_app' ) ) {
                $data[ 'ACCOUNTING_ENABLED' ] = 'true';
            }
            else {
                $data[ 'ACCOUNTING_ENABLED' ] = 'false';
                $data[ 'MAIN_APP' ]           = 'false';
            }
            $data[ 'APP_ENV' ]                = 'production';
            $data[ 'IO_TEC_SECRET' ]          = 'IO-Xp9TIqAuNGDBBWgGCBbDIIyLLpabbliJ9';
            $data[ 'IO_TEC_CLIENT_ID' ]       = 'pay-ef3f5d3f-5489-4b3b-a295-9ba483c18f4f';
            $data[ 'IO_TEC_WALLET_ID' ]       = 'bc50d942-3890-4829-baec-3614e4297274';
            $data[ 'TIMEZONE' ]               = 'Africa/Kampala';
            $data[ 'CURRENCY' ]               = Settings::group( 'site' )->get( 'site_default_currency' );
            $data[ 'CURRENCY_SYMBOL' ]        = Settings::group( 'site' )->get( 'site_default_currency_symbol' );
            $data[ 'CURRENCY_DECIMAL_POINT' ] = 0;
            $data[ 'DEMO' ]                   = 'true';
            $data[ 'MAIL_HOST' ]              = 'smtp.zoho.com';
            $data[ 'MAIL_PORT' ]              = 465;
            $data[ 'MAIL_USERNAME' ]          = 'services@smartduuka.com';
            $data[ 'MAIL_PASSWORD' ]          = 'Nz0VjVhzC6Xk';
            $data[ 'MAIL_ENCRYPTION' ]        = 'ssl';
            $data[ 'MAIL_FROM_ADDRESS' ]      = 'services@smartduuka.com';
            $data[ 'MAIL_FROM_NAME' ]         = 'Smart Duuka Software';
            $data[ 'QUEUE_CONNECTION' ]       = 'database';

            if ( ! $env_editor->keyExists( 'PROJECT_ID' ) ) {
                $data[ 'PROJECT_ID' ] = Str::uuid()->getHex();
            }
            $email = settingValue( key: 'company_email' , group: 'company' );
            if ( $email && ! config( 'app.main_app' ) ) {
                $env_editor->addData( [ 'MAIL_REPLY_ADDRESS' => $email ] );
            }
            if ( ! $env_editor->keyExists( 'BUSINESS_ID' ) ) {
                $data[ 'BUSINESS_ID' ] = time();
            }

            if ( ! config( 'app.dev' ) ) {
                $data[ 'DEV' ]                = 'false';
                $data[ 'DB_DATABASE_SECOND' ] = 'demo@smartduuka';
                $data[ 'DB_USERNAME_SECOND' ] = 'demo@smartduuka';
                $data[ 'DB_PASSWORD_SECOND' ] = 'jrtjK4JUVMGDStZ';
                $data[ 'CHROME_PATH' ]        = '/usr/bin/google-chrome';
            }
            $data[ 'MAIN_APP' ] = 'demo@smartduuka';
            $env_editor->addData( $data );
            Artisan::call( 'optimize:clear' );
            PersonalAccessToken::truncate();
            $this->call( 'table:truncate' , [ 'table' => 'menus' ] );
            $this->call( 'db:seed' , [ '--class' => 'MenuTableSeeder' , '--force' => TRUE ] );
            $this->call( 'table:truncate' , [ 'table' => 'permissions' ] );
            $this->call( 'db:seed' , [ '--class' => 'PermissionTableSeeder' , '--force' => TRUE ] );
            $permission       = DB::table( 'permissions' )->where( 'name' , 'settings' )->first();
            $module_warehouse = Settings::group( 'module' )->get( 'module_warehouse' );
            $supplier         = Supplier::first();
            if ( ! $supplier ) {
                Supplier::create( [
                    'company'      => 'Company' ,
                    'name'         => 'Supplier' ,
                    'country_code' => '+256' ,
                    'phone'        => '701234567'
                ] );
            }
            Subscription::where( 'project_id' , config( 'app.project_id' ) )->update( [ 'business_id' => config( 'app.business_id' ) ] );
            Order::where( 'paid' , '=' , NULL )->update( [ 'paid' => 0 ] );
            Order::query()->update( [ 'balance' => DB::raw( 'total - paid' ) ] );
            Order::where( 'balance' , '<' , 0 )->update( [ 'balance' => 0 ] );
            $business = Business::firstOrCreate(
                [ 'project_id' => config( 'app.project_id' ) ] ,
                [
                    'business_id'   => config( 'app.business_id' ) ,
                    'business_name' => Settings::group( 'company' )->get( 'company_name' )
                ] );
            $business->update( [ 'phone_number' => phoneNumber() ] );
            Currency::where( 'exchange_rate' , NULL )->update( [ 'exchange_rate' => 1 ] );
            $default_currency_symbol = Settings::group( 'site' )->get( 'site_default_currency_symbol' );
            $default_currency        = Currency::where( 'symbol' , $default_currency_symbol )->first();
            foreach ( PaymentMethod::all() as $payment_method ) {
                if ( $default_currency ) {
                    PaymentAccount::firstOrCreate(
                        [ 'name' => $payment_method->name ] ,
                        [ 'name' => $payment_method->name , 'currency_id' => $default_currency->id ] );
                }
            }
            updateCoa();
            if ( $module_warehouse == 1 ) {
                $stocks               = Stock::where( 'warehouse_id' , NULL )->get();
                $purchases            = Purchase::where( 'warehouse_id' , NULL )->get();
                $production_processes = ProductionProcess::where( 'warehouse_id' , NULL )->get();
                if ( $stocks ) {
                    $warehouse = Warehouse::first();
                    if ( ! $warehouse ) {
                        $warehouse = Warehouse::create( [
                            'name'         => 'Shop Storage' ,
                            'email'        => NULL ,
                            'deletable'    => FALSE ,
                            'phone'        => NULL ,
                            'location'     => NULL ,
                            'country_code' => NULL ,
                        ] );
                    }
                    $warehouse->update( [ 'deletable' => FALSE ] );
                    foreach ( $stocks as $stock ) {
                        $stock->update( [ 'warehouse_id' => $warehouse->id ] );
                    }
                    foreach ( $purchases as $purchase ) {
                        $purchase->update( [ 'warehouse_id' => $warehouse->id ] );
                    }
                    foreach ( $production_processes as $production_process ) {
                        $production_process->update( [ 'warehouse_id' => $warehouse->id ] );
                    }
                }
            }
            if ( ! $permission ) {
                return 0;
            }
            $adminRole = Role::find( EnumRole::ADMIN );
            $adminRole?->givePermissionTo( Permission::all() );
            ExpenseCategory::firstOrCreate( [
                'user_id' => 0 ,
                'name'    => 'Commission Payouts' ,
                'status'  => Status::ACTIVE ,
            ] );
            return 1;
        }
    }
