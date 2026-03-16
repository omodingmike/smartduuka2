<?php

    namespace App\Console\Commands;

    use App\Enums\Role;
    use App\Models\User;
    use Illuminate\Console\Command;

    class FixUserRoles extends Command
    {
        protected $signature = 'app:fix-user-roles';

        protected $description = 'Change all user roles to customer except for a specific user.';

        public function handle() : void
        {
            $this->info( 'Fixing user roles...' );

            User::where( 'email' , '!=' , 'sandramuhumuza53@gmail.com' )->chunk( 100 , function ($users) {
                foreach ( $users as $user ) {
                    $user->syncRoles( [ Role::CUSTOMER ] );
                    $this->info( "User {$user->email} role changed to customer." );
                }
            } );

            $this->info( 'User roles fixed successfully.' );
        }
    }
