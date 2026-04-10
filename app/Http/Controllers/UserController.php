<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user();
        $permissions = $user->getAllPermissions();
        $user->unsetRelation('permissions');
        $user->setAttribute('permissions', $permissions);

        return $user;
    }
}
