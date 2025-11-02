<?php

    namespace App\Http\Controllers\Admin;


    use App\Exports\DiningTableExport;
    use App\Http\Requests\DiningTableRequest;
    use App\Http\Requests\PaginateRequest;
    use App\Http\Resources\DiningTableResource;
    use App\Models\DiningTable;
    use App\Services\DiningTableService;
    use Exception;
    use Illuminate\Contracts\Foundation\Application;
    use Illuminate\Contracts\Routing\ResponseFactory;
    use Illuminate\Http\Request;
    use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
    use Illuminate\Http\Response;
    use Maatwebsite\Excel\Facades\Excel;

    class DiningTableController extends AdminController
    {
        private DiningTableService $diningTableService;

        public function __construct(DiningTableService $diningTable)
        {
            parent::__construct();
            $this->diningTableService = $diningTable;
            $this->middleware([ 'permission:dining-tables' ])->only('export');
            $this->middleware([ 'permission:dining_tables_create' ])->only('store');
            $this->middleware([ 'permission:dining_tables_edit' ])->only('update');
            $this->middleware([ 'permission:dining_tables_delete' ])->only('destroy');
            $this->middleware([ 'permission:dining_tables_show' ])->only('show');
        }

        public function index(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return DiningTableResource::collection($this->diningTableService->list($request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function all(PaginateRequest $request) : Response | AnonymousResourceCollection | Application | ResponseFactory
        {
            try {
                return DiningTableResource::collection(Diningtable::all());
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }


        public function store(
            DiningTableRequest $request
        ) : Response | DiningTableResource | Application | ResponseFactory {
            try {
                return new DiningTableResource($this->diningTableService->store($request));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function show(
            DiningTable $diningTable
        ) : Response | DiningTableResource | Application | ResponseFactory {
            try {
                return new DiningTableResource($this->diningTableService->show($diningTable));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function update(
            DiningTableRequest $request ,
            DiningTable $diningTable
        ) : Response | DiningTableResource | Application | ResponseFactory {
            try {
                return new DiningTableResource($this->diningTableService->update($request , $diningTable));
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function changeStatus(
            Request $request ,
            DiningTable $diningTable
        )  {
            try {
               $diningTable->update(['status' => $request->status]);
                return response([ 'status' => true , 'message' => 'Status updated successfully' ] , 200);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function destroy(
            DiningTable $diningTable
        ) : Response | Application | ResponseFactory {
            try {
                $this->diningTableService->destroy($diningTable);
                return response('' , 202);
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }

        public function export(PaginateRequest $request) : Response | \Symfony\Component\HttpFoundation\BinaryFileResponse | Application | ResponseFactory
        {
            try {
                return Excel::download(new DiningTableExport($this->diningTableService , $request) , 'Dining-Table.xlsx');
            } catch ( Exception $exception ) {
                return response([ 'status' => false , 'message' => $exception->getMessage() ] , 422);
            }
        }
    }
