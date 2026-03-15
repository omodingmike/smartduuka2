<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function user(Request $request)
    {
        $user = $request->user();

        // 1. Fetch all direct & inherited permissions, and extract just the names
        $permissions = $user->getAllPermissions();

        // 2. Unset the empty Spatie relationship so it doesn't overwrite your custom attribute
        $user->unsetRelation('permissions');

        // 3. Attach the flat array of permission names
        $user->setAttribute('permissions', $permissions);

        return $user;
    }
}
