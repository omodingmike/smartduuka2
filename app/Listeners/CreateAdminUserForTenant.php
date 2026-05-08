<?php

    namespace App\Listeners;

    use App\Enums\Status;
    use App\Http\Requests\AdministratorRequest;
    use App\Models\BusinessOnBoard;
    use App\Models\Tenant;
    use App\Services\AdministratorService;
    use App\Services\PinService;
    use Illuminate\Support\Facades\DB;

    class CreateAdminUserForTenant
    {
        public function __construct(public Tenant $tenant) {}

        public function handle(AdministratorService $administrator_service , PinService $pin_service) : void
        {
            try {
                $tenant   = $this->tenant->getTenantKey();
                $on_board = BusinessOnBoard::where( 'tenant' , $tenant )->first();
                tenancy()->initialize( $tenant );
                DB::transaction( function () use ($on_board , $administrator_service , $pin_service) {
                    if ( $on_board ) {
                        $admin_request = new AdministratorRequest();
                        $admin_request->merge( [
                            'name'             => $on_board->admin_name ,
                            'email'            => $on_board->admin_email ,
                            'password'         => $on_board->admin_password ,
                            'phone'            => $on_board->phone ,
                            'status'           => Status::ACTIVE->value ,
                            'pin'              => $on_board->admin_pin ,
                            'forceReset'       => TRUE ,
                            'emailCredentials' => TRUE ,
                        ] );
                        $administrator_service->store( $admin_request , $pin_service );
                    }
                } );
                tenancy()->end();

            } catch ( \Exception $e ) {
                info( $e->getMessage() );
            }
        }
    }