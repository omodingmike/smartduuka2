<?php

    namespace App\Http\Controllers\Admin;

    use App\Http\Requests\PaginateRequest;
    use App\Http\Requests\RoleRequest;
    use App\Http\Resources\RoleResource;
    use App\Services\RoleService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Spatie\Permission\Models\Role;

    class RoleController extends AdminController
    {
        private RoleService $roleService;

        public function __construct(RoleService $roleService)
        {
            parent::__construct();
            $this->roleService = $roleService;
            $this->middleware([ 'permission:settings' ])->only('show' , 'store' , 'update' , 'destroy');
        }

        public function index(PaginateRequest $request) : Application | Response | \Illuminate\Http\Resources\Json\AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return RoleResource::collection($this->roleService->list($request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function show(Role $role) : RoleResource | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new RoleResource($this->roleService->show($role));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function store(RoleRequest $request) : RoleResource | Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new RoleResource($this->roleService->store($request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function update(RoleRequest $request , Role $role) : RoleResource | Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new RoleResource($this->roleService->update($request , $role));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function destroy(Role $role) : Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $this->roleService->destroy($role);
                return response('' , 202);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }
    }
