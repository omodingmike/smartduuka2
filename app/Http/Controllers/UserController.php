<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;

    class UserController extends Controller
    {
        public function user(Request $request)
        {
            try {
                $user        = $request->user();
                $permissions = $user->getAllPermissions();
                $user->unsetRelation( 'permissions' );
                $user->setAttribute( 'permissions' , $permissions );
                return $user;
            } catch ( \Exception $e ) {
                throw new \Exception( $e->getMessage() , 422 );
            }
        }

        public function centralUser(Request $request)
        {
            try {
                $user = $request->user();
//                $permissions = $user->getAllPermissions();
//                $user->unsetRelation( 'permissions' );
//                $user->setAttribute( 'permissions' , $permissions );
                return $user;
            } catch ( \Exception $e ) {
                throw new \Exception( $e->getMessage() , 422 );
            }
        }

    }
