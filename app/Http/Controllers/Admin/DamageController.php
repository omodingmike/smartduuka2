<?php

    namespace App\Http\Controllers\Admin;

    use App\Enums\Status;
    use App\Exports\DamageExport;
    use App\Http\Requests\DamageRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\DamageDetailsResource;
    use App\Http\Resources\DamageResource;
    use App\Models\Damage;
    use App\Services\DamageService;
    use Exception;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Foundation\Application;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Maatwebsite\Excel\Facades\Excel;

    class DamageController extends AdminController
    {
        public DamageService $damageService;

        public function __construct(DamageService $damageService)
        {
            parent::__construct();
            $this->damageService = $damageService;
            $this->middleware( [ 'permission:damages' ] )->only( 'export' , 'downloadAttachment' );
            $this->middleware( [ 'permission:damage_create' ] )->only( 'store' );
            $this->middleware( [ 'permission:damage_edit' ] )->only( 'edit' , 'update' );
            $this->middleware( [ 'permission:damage_delete' ] )->only( 'destroy' );
            $this->middleware( [ 'permission:damage_show' ] )->only( 'show' );
        }

        public function index(PaginateRequest $request) : Application | Response | AnonymousResourceCollection | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return DamageResource::collection( $this->damageService->list( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function store(DamageRequest $request) : Application | Response | DamageResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new DamageResource( $this->damageService->store( $request ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function show(Damage $damage) : Application | Response | DamageDetailsResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new DamageDetailsResource( $this->damageService->show( $damage ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function updateStatus(Request $request , Damage $damage)
        {
            try {
                $damage->update( [ 'status' => $request->input( 'status' ) ] );
                $damage->stocks()->update( [ 'status' => Status::INACTIVE ] );
                return response()->json( [] );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function edit(Damage $damage) : Application | Response | DamageDetailsResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new DamageDetailsResource( $this->damageService->edit( $damage ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function update(DamageRequest $request , Damage $damage) : Application | Response | DamageResource | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return new DamageResource( $this->damageService->update( $request , $damage ) );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function destroy(Damage $damage) : Application | Response | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                $this->damageService->destroy( $damage );
                return response( '' , 202 );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function export(PaginateRequest $request) : Application | Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | \Illuminate\Contracts\Foundation\Application | ResponseFactory
        {
            try {
                return Excel::download( new DamageExport( $this->damageService , $request ) , 'Damages.xlsx' );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }

        public function downloadAttachment(Damage $damage)
        {
            try {
                return $this->damageService->downloadAttachment( $damage );
            } catch ( Exception $exception ) {
                return response( [ 'status' => FALSE , 'message' => $exception->getMessage() ] , 422 );
            }
        }
    }
