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

            $adminEmails    = [
                'ajnakaweesa@gmail.com' ,
                'support@smartduuka.com' , 'juhudicc@gmail.com'
            ];
            $employeeEmails = [
                'sanodhi@gmail.com' ,
            ];

            // 1. Assign Admin role to the specific users
            $admins    = User::whereIn( 'email' , $adminEmails )->get();
            $employees = User::whereIn( 'email' , $employeeEmails )->get();

            foreach ( $admins as $admin ) {
                $admin->syncRoles( [ Role::ADMIN ] );
                $this->info( "User {$admin->email} role changed to Admin." );
            }
            foreach ( $employees as $employee ) {
                $employee->syncRoles( [ Role::STAFF ] );
                $this->info( "User {$employee->email} role changed to Admin." );
            }

            // 2. Assign Customer role to everyone else
            User::where( function ($query) use ($adminEmails , $employeeEmails) {
                $query->whereNotIn( 'email' , $adminEmails )
                      ->whereNotIn( 'email' , $employeeEmails )
                      ->orWhereNull( 'email' );
            } )->chunkById( 100 , function ($users) {
                foreach ( $users as $user ) {
                    $user->syncRoles( [ Role::CUSTOMER ] );

                    // Added a fallback so the console outputs something if the email is null
                    $identifier = $user->email ?? "ID: {$user->id} (No Email)";
                    $this->info( "User {$identifier} role changed to Customer." );
                }
            } );

            $this->info( 'User roles fixed successfully.' );
        }
    }