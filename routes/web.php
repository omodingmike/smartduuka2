<?php

    use App\Enums\Ask;
    use App\Enums\Role as EnumRole;
    use App\Enums\Status;
    use App\Models\User;
    use Illuminate\Support\Facades\Route;
    use Spatie\Permission\Models\Role;

    Route::get( '/' , function () {
        $admin = User::create( [
            'name'              => 'Omoding mike' ,
            'email'             => 'omodingmike@gmail.com' ,
            'phone'             => '1254875855' ,
            'username'          => 'Omoding' ,
            'email_verified_at' => now() ,
            'password'          => bcrypt( '123456' ) ,
            'status'            => Status::ACTIVE ,
            'country_code'      => '+880' ,
            'is_guest'          => Ask::NO
        ] );
        $admin->assignRole( Role::find( EnumRole::ADMIN ) );
        return [ 'Laravel' => app()->version() ];
    } );

    require __DIR__ . '/auth.php';
