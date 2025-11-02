<?php

    namespace App\Http\Controllers\Admin;


    use App\Http\Requests\PermissionRequest;
    use App\Http\Resources\RoleResource;
    use App\Libraries\AppLibrary;
    use App\Models\User;
    use App\Services\PermissionService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\JsonResponse;
    use Illuminate\Http\Response;
    use Spatie\Permission\Models\Permission;
    use Spatie\Permission\Models\Role;


    class PermissionController extends AdminController
    {
        private PermissionService $permissionService;

        public function __construct(PermissionService $permissionService)
        {
            parent::__construct();
            $this->permissionService = $permissionService;
            $this->middleware( [ 'permission:settings' ] )->only( 'update' );
        }

        public function index(Role $role) : Application | Response | JsonResponse | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $permissions     = Permission::get();
                $rolePermissions = Permission::join(
                    "role_has_permissions" ,
                    "role_has_permissions.permission_id" ,
                    "=" ,
                    "permissions.id"
                )->where( "role_has_permissions.role_id" , $role->id )->get()->pluck( 'name' , 'id' );
                $permissions     = AppLibrary::permissionWithAccess( $permissions , $rolePermissions );
                $permissions     = AppLibrary::numericToAssociativeArrayBuilder( $permissions->toArray() );
                $role->users()->each( function (User $user) {
                    $user->tokens()->delete();
                } );
                return new JsonResponse( [ 'data' => $permissions ] , 201 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(PermissionRequest $request , Role $role) : RoleResource | Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new RoleResource( $this->permissionService->update( $request , $role ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
