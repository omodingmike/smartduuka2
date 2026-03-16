<?php

    namespace App\Console\Commands;

    use App\Enums\Role;
    use App\Models\User;
    use Illuminate\Console\Command;

    class FixUserRoles extends Command
    {
        protected $signature = 'app:fix-user-roles';

        protected $description = 'Assign Admin role to specific users and Customer role to everyone else.';

        public function handle() : void
        {
            $this->info( 'Fixing user roles...' );

            $adminEmails = [
                'sandramuhumuza53@gmail.com' ,
                'support@smartduuka.com'
            ];

            // 1. Assign Admin role to the specific users
            $admins = User::whereIn( 'email' , $adminEmails )->get();

            foreach ( $admins as $admin ) {
                // syncRoles safely replaces all existing roles with Admin
                $admin->syncRoles( [ Role::ADMIN ] );
                $this->info( "User {$admin->email} role changed to Admin." );
            }

            // 2. Assign Customer role to everyone else
            User::whereNotIn( 'email' , $adminEmails )->chunk( 100 , function ($users) {
                foreach ( $users as $user ) {
                    // syncRoles safely replaces all existing roles with Customer
                    $user->syncRoles( [ Role::CUSTOMER ] );
                    $this->info( "User {$user->email} role changed to Customer." );
                }
            } );

            $this->info( 'User roles fixed successfully.' );
        }
    }